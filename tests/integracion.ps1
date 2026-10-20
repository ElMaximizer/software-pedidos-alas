param(
    [string]$BaseUrl = 'http://localhost/software-pedidos-alas/',
    [string]$Mysql = 'C:\xampp\mysql\bin\mysql.exe',
    [string]$CedulaAdministrador = '99999999',
    [string]$ContrasenaAdministrador = 'AlasDemo2026!',
    [switch]$ProbarImagen
)

# Ejecutar en PowerShell 7 contra una base local de desarrollo con datos_demo.
# Los datos se identifican con una marca aleatoria y se retiran al terminar.
$ErrorActionPreference = 'Stop'
$raizProyecto = Split-Path $PSScriptRoot -Parent
$baseAlasTest = $BaseUrl.TrimEnd('/') + '/'
$mysqlAlasTest = $Mysql
$tagAlasTest = 'QA' + [guid]::NewGuid().ToString('N').Substring(0,12)
$emailAlasTest = $tagAlasTest + '@example.invalid'
$cedulaAlasTest = 80000000 + (Get-Random -Maximum 10000000)
$adminAlasTest = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$publicAlasTest = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$idProductoAlasTest = $null
$idProductoExtraAlasTest = $null
$idPedidoAlasTest = $null
$idUsuarioAlasTest = $null
$archivoImagenAlasTest = $null
function DbAlasTest([string]$query) {
    $output = & $mysqlAlasTest --user=root --host=localhost --default-character-set=utf8mb4 --batch --skip-column-names --execute=$query
    if ($LASTEXITCODE -ne 0) { throw 'Fallo en consulta de verificacion.' }
    return ($output -join "`n").Trim()
}
function PageAlasTest([string]$path, $session, $body = $null) {
    $parameters = @{ Uri = $baseAlasTest + $path; WebSession = $session; TimeoutSec = 15 }
    if ($null -ne $body) {
        $parameters.Method = 'Post'
        $pairs = foreach ($entry in $body.GetEnumerator()) { foreach ($value in @($entry.Value)) { [uri]::EscapeDataString($entry.Key) + '=' + [uri]::EscapeDataString([string]$value) } }
        $parameters.Body = $pairs -join '&'
        $parameters.ContentType = 'application/x-www-form-urlencoded'
    }
    $response = Invoke-WebRequest @parameters
    if ($response.Content -match 'Fatal error|Warning:|Notice:') { throw "PHP en $path" }
    return $response
}
function TokenAlasTest($response) { return [regex]::Match($response.Content, 'name="csrf_token" value="([^"]+)"').Groups[1].Value }
function NonceAlasTest($response) { return [regex]::Match($response.Content, 'name="pedido_nonce" value="([^"]+)"').Groups[1].Value }
function VersionAlasTest($response) { return [regex]::Match($response.Content, 'name="pedido_version" value="([^"]+)"').Groups[1].Value }
function AssertAlasTest($condition, $message) { if (-not $condition) { throw $message } }
try {
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.solicitud_usuario WHERE cedula=$cedulaAlasTest") -eq '0') 'Cedula de prueba ocupada.'
    $page = PageAlasTest 'app/Views/login.php' $adminAlasTest
    $page = PageAlasTest 'app/Views/login.php' $adminAlasTest @{csrf_token=(TokenAlasTest $page);cedula=$CedulaAdministrador;password=$ContrasenaAdministrador}
    AssertAlasTest ($page.Content.Contains('Panel de administraci')) 'No se pudo iniciar sesion demo.'
    Write-Output 'OK acceso y panel con datos reales.'
    foreach ($path in @('app/Views/productos.php','app/Views/pedidos.php','app/Views/accesos.php','app/Views/correos.php','app/Views/consultas.php')) { $null = PageAlasTest $path $adminAlasTest }
    foreach ($path in @('app/Views/producto-form.php','app/Views/pedido-form.php')) {
        $respuestaInterna = Invoke-WebRequest ($baseAlasTest + $path) -WebSession $adminAlasTest -SkipHttpErrorCheck
        AssertAlasTest ($respuestaInterna.StatusCode -eq 404 -and $respuestaInterna.Content -notmatch 'Undefined variable|Warning:') 'El formulario interno no devolvio un 404 limpio.'
    }
    foreach ($paginaInvalida in @('999999999999999999999','9223372036854775807','-10','texto')) {
        $null = PageAlasTest ('app/Views/pedidos.php?pagina=' + $paginaInvalida) $adminAlasTest
    }
    $page = PageAlasTest 'app/Views/agregar-producto.php' $adminAlasTest
    $form = @{csrf_token=(TokenAlasTest $page);nombre=$tagAlasTest;categoria='Panes';descripcion='Producto temporal QA';precio='15.25';presentacion='Unidad';ingredientes='Harina';alergenos='Gluten';activo='1'}
    if ($ProbarImagen) {
        $form.imagen = Get-Item (Join-Path $raizProyecto 'public/images/panes.png')
        $page = Invoke-WebRequest ($baseAlasTest + 'app/Views/agregar-producto.php') -Method Post -WebSession $adminAlasTest -Form $form -TimeoutSec 15
    } else {
        $page = PageAlasTest 'app/Views/agregar-producto.php' $adminAlasTest $form
    }
    $idProductoAlasTest = DbAlasTest "SELECT id_producto FROM alas_bd.productos WHERE nombre='$tagAlasTest'"
    AssertAlasTest ($idProductoAlasTest -match '^\d+$') 'No se guardo el producto con imagen.'
    if ($ProbarImagen) {
        $archivoImagenAlasTest = DbAlasTest "SELECT imagen FROM alas_bd.productos WHERE id_producto=$idProductoAlasTest"
        AssertAlasTest ($archivoImagenAlasTest -match '^uploads/productos/[a-f0-9]+\.png$') 'Imagen no guardada de forma segura.'
        $null = PageAlasTest ('public/' + $archivoImagenAlasTest) $publicAlasTest
    }
    $page = PageAlasTest ('public/productos.php?q=' + $tagAlasTest) $publicAlasTest
    AssertAlasTest ($page.Content.Contains($tagAlasTest)) 'Producto no visible en catalogo.'
    $page = PageAlasTest 'public/productos.php?q=NOEXISTEESTEPRODUCTOQA' $publicAlasTest
    AssertAlasTest ($page.Content.Contains('No encontramos ese producto')) 'Estado vacio no funciona.'
    Write-Output 'OK catalogo, filtros y estado vacio (imagen opcional).'
    $page = PageAlasTest 'app/Views/agregar-pedido.php' $adminAlasTest
    $segundaPestanaCrear = PageAlasTest 'app/Views/agregar-pedido.php' $adminAlasTest
    AssertAlasTest ((NonceAlasTest $page) -ne (NonceAlasTest $segundaPestanaCrear)) 'Dos formularios comparten el mismo identificador.'
    $tokenAdmin = TokenAlasTest $page
    $otherProduct = DbAlasTest "SELECT id_producto FROM alas_bd.productos WHERE nombre='Bizcocho de chocolate' LIMIT 1"
    $body = @{csrf_token=$tokenAdmin;pedido_nonce=(NonceAlasTest $page);cliente_nombre=$tagAlasTest;telefono='099000000';fecha_retiro=(Get-Date).AddDays(1).ToString('yyyy-MM-dd');hora_retiro='10:30';notas='Prueba de varias lineas';'producto_id[]'=@($idProductoAlasTest,$otherProduct);'cantidad[]'=@('2','3')}
    $page = PageAlasTest 'app/Views/agregar-pedido.php' $adminAlasTest $body
    $idPedidoAlasTest = DbAlasTest "SELECT id_pedido FROM alas_bd.pedidos WHERE cliente_nombre='$tagAlasTest' ORDER BY id_pedido DESC LIMIT 1"
    AssertAlasTest ($idPedidoAlasTest -match '^\d+$') ('No se registro pedido: ' + $page.Content.Substring(0,[Math]::Min(300,$page.Content.Length)))
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.pedido_detalles WHERE id_pedido=$idPedidoAlasTest") -eq '2') 'Faltan lineas.'
    AssertAlasTest ((DbAlasTest "SELECT total FROM alas_bd.pedidos WHERE id_pedido=$idPedidoAlasTest") -eq '225.50') 'Total incorrecto.'
    $null = PageAlasTest 'app/Views/agregar-pedido.php' $adminAlasTest $body
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.pedidos WHERE cliente_nombre='$tagAlasTest'") -eq '1') 'Se duplico el pedido al reenviar.'
    $otroPedido = $body.Clone()
    $otroPedido.cliente_nombre = $tagAlasTest + 'OTRO'
    $otroPedido.pedido_nonce = NonceAlasTest $segundaPestanaCrear
    $null = PageAlasTest 'app/Views/agregar-pedido.php' $adminAlasTest $otroPedido
    $idOtroPedido = DbAlasTest "SELECT id_pedido FROM alas_bd.pedidos WHERE cliente_nombre='${tagAlasTest}OTRO'"
    AssertAlasTest ($idOtroPedido -match '^\d+$') 'Guardar en una pestana invalido el formulario de la otra.'
    $page = PageAlasTest ('app/Views/editar-producto.php?id=' + $idProductoAlasTest) $adminAlasTest
    $null = PageAlasTest ('app/Views/editar-producto.php?id=' + $idProductoAlasTest) $adminAlasTest @{csrf_token=(TokenAlasTest $page);id_producto=$idProductoAlasTest;nombre=$tagAlasTest;categoria='Panes';descripcion='Actualizado';precio='25.75';presentacion='Unidad';ingredientes='Harina';alergenos='Gluten';activo='1'}
    $page = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idPedidoAlasTest) $adminAlasTest
    $pestanaDesactualizada = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idPedidoAlasTest) $adminAlasTest
    $pestanaOtroPedido = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idOtroPedido) $adminAlasTest
    $body.csrf_token = TokenAlasTest $page; $body.pedido_nonce = NonceAlasTest $page; $body.pedido_version = VersionAlasTest $page; $body.id_pedido = $idPedidoAlasTest; $body.estado = 'En preparación'
    $null = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idPedidoAlasTest) $adminAlasTest $body
    AssertAlasTest ((DbAlasTest "SELECT total FROM alas_bd.pedidos WHERE id_pedido=$idPedidoAlasTest") -eq '225.50') 'Se perdio el precio historico al editar.'
    $otroPedido.id_pedido = $idOtroPedido
    $otroPedido.pedido_nonce = NonceAlasTest $pestanaOtroPedido
    $otroPedido.pedido_version = VersionAlasTest $pestanaOtroPedido
    $otroPedido.telefono = '------'
    $rechazoTelefono = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idOtroPedido) $adminAlasTest $otroPedido
    AssertAlasTest ($rechazoTelefono.Content.Contains('contacto v')) 'Se acepto un telefono sin digitos.'
    $otroPedido.telefono = '099000000'
    $null = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idOtroPedido) $adminAlasTest $otroPedido
    AssertAlasTest ((DbAlasTest "SELECT version FROM alas_bd.pedidos WHERE id_pedido=$idOtroPedido") -eq '2') 'No se pudo guardar el segundo pedido o corregir una validacion.'
    $datosAnteriores = $body.Clone()
    $datosAnteriores.pedido_nonce = NonceAlasTest $pestanaDesactualizada
    $datosAnteriores.pedido_version = VersionAlasTest $pestanaDesactualizada
    $datosAnteriores.telefono = '099111111'
    $rechazoVersion = PageAlasTest ('app/Views/editar-pedido.php?id=' + $idPedidoAlasTest) $adminAlasTest $datosAnteriores
    AssertAlasTest ($rechazoVersion.Content.Contains('fue modificado')) 'Un formulario desactualizado sobrescribio el pedido.'
    AssertAlasTest ((DbAlasTest "SELECT telefono FROM alas_bd.pedidos WHERE id_pedido=$idPedidoAlasTest") -eq '099000000') 'Se aplicaron datos de una version anterior.'
    $page = PageAlasTest ('app/Views/eliminar-pedido.php?id=' + $idPedidoAlasTest) $adminAlasTest
    $null = PageAlasTest ('app/Views/eliminar-pedido.php?id=' + $idPedidoAlasTest) $adminAlasTest @{csrf_token=(TokenAlasTest $page);pedido_nonce=(NonceAlasTest $page);pedido_version=(VersionAlasTest $page);id_pedido=$idPedidoAlasTest;motivo_cancelacion='Error de registro'}
    AssertAlasTest ((DbAlasTest "SELECT estado FROM alas_bd.pedidos WHERE id_pedido=$idPedidoAlasTest") -eq 'Cancelado') 'No se cancelo el pedido.'
    Write-Output 'OK pedido de varias lineas, precios historicos, reenvio y cancelacion.'
    $page = PageAlasTest ('public/producto.php?id=' + $idProductoAlasTest) $publicAlasTest
    $null = PageAlasTest 'public/carrito.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);accion='agregar';id_producto=$idProductoAlasTest;cantidad='2'}
    $pageExtra = PageAlasTest 'app/Views/agregar-producto.php' $adminAlasTest
    $null = PageAlasTest 'app/Views/agregar-producto.php' $adminAlasTest @{csrf_token=(TokenAlasTest $pageExtra);nombre=($tagAlasTest+'SECUNDARIO');categoria='Panes';descripcion='Producto temporal extra';precio='5.00';presentacion='Unidad';ingredientes='Harina';alergenos='Gluten';activo='1'}
    $idProductoExtraAlasTest = DbAlasTest "SELECT id_producto FROM alas_bd.productos WHERE nombre='${tagAlasTest}SECUNDARIO'"
    $page = PageAlasTest 'public/carrito.php' $publicAlasTest
    $null = PageAlasTest 'public/carrito.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);accion='agregar';id_producto=$idProductoExtraAlasTest;cantidad='1'}
    $confirmacionAnterior = PageAlasTest 'public/realizar-pedido.php' $publicAlasTest
    $pageExtra = PageAlasTest ('app/Views/eliminar-producto.php?id=' + $idProductoExtraAlasTest) $adminAlasTest
    $null = PageAlasTest ('app/Views/eliminar-producto.php?id=' + $idProductoExtraAlasTest) $adminAlasTest @{csrf_token=(TokenAlasTest $pageExtra);id_producto=$idProductoExtraAlasTest}
    $rechazoCarrito = PageAlasTest 'public/realizar-pedido.php' $publicAlasTest @{csrf_token=(TokenAlasTest $confirmacionAnterior);pedido_nonce=(NonceAlasTest $confirmacionAnterior);cliente_nombre=($tagAlasTest+'PUBLICO');telefono='099000000';fecha_retiro=(Get-Date).AddDays(1).ToString('yyyy-MM-dd');hora_retiro='11:30';notas='Pedido publico QA'}
    AssertAlasTest ($rechazoCarrito.Content.Contains('El pedido no se registr')) 'No se aviso que faltaba un producto.'
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.pedidos WHERE cliente_nombre='${tagAlasTest}PUBLICO'") -eq '0') 'Se registro un pedido publico incompleto.'
    $null = PageAlasTest 'public/carrito.php' $publicAlasTest
    $page = PageAlasTest 'public/realizar-pedido.php' $publicAlasTest
    $null = PageAlasTest 'public/realizar-pedido.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);pedido_nonce=(NonceAlasTest $page);cliente_nombre=($tagAlasTest+'PUBLICO');telefono='099000000';fecha_retiro=(Get-Date).AddDays(1).ToString('yyyy-MM-dd');hora_retiro='11:30';notas='Pedido publico QA'}
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.pedidos WHERE cliente_nombre='${tagAlasTest}PUBLICO'") -eq '1') 'No se confirmo el carrito publico.'
    AssertAlasTest ((DbAlasTest "SELECT total FROM alas_bd.pedidos WHERE cliente_nombre='${tagAlasTest}PUBLICO'") -eq '51.50') 'El pedido no coincide con el carrito revisado.'
    $page = PageAlasTest 'public/carrito.php' $publicAlasTest
    AssertAlasTest ($page.Content.Contains('Tu carrito est')) 'No se vacio el carrito.'
    $page = PageAlasTest 'public/contacto.php' $publicAlasTest
    $null = PageAlasTest 'public/contacto.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);nombre=$tagAlasTest;email=$emailAlasTest;motivo='Disponibilidad';mensaje='Consulta temporal de prueba'}
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.mensajes_contacto WHERE nombre='$tagAlasTest'") -eq '1') 'No se guardo la consulta.'
    Write-Output 'OK carrito, confirmacion publica y contacto persistente.'
    $page = PageAlasTest 'app/Views/register.php' $publicAlasTest
    $null = PageAlasTest 'app/Views/register.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);nombre=$tagAlasTest;cedula=$cedulaAlasTest;email=$emailAlasTest;password='AlasTest2026!';confirm_password='AlasTest2026!'}
    $idUsuarioAlasTest = DbAlasTest "SELECT id_sol FROM alas_bd.solicitud_usuario WHERE cedula=$cedulaAlasTest"
    AssertAlasTest ($idUsuarioAlasTest -match '^\d+$') 'No se guardo la solicitud de acceso.'
    $page = PageAlasTest 'app/Views/login.php' $publicAlasTest
    $page = PageAlasTest 'app/Views/login.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);cedula=$cedulaAlasTest;password='AlasTest2026!'}
    AssertAlasTest ($page.Content.Contains('todav')) 'Se permitio el acceso sin aprobacion.'
    $page = PageAlasTest 'app/Views/accesos.php' $adminAlasTest
    $null = PageAlasTest 'app/Views/accesos.php' $adminAlasTest @{csrf_token=(TokenAlasTest $page);id_usuario=$idUsuarioAlasTest;accion='aprobar'}
    $page = PageAlasTest 'app/Views/recuperar.php' $publicAlasTest
    $null = PageAlasTest 'app/Views/recuperar.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);email=$emailAlasTest}
    $mail = DbAlasTest "SELECT cuerpo FROM alas_bd.correos_locales WHERE destinatario='$emailAlasTest' ORDER BY id_correo DESC LIMIT 1"
    $resetToken = [regex]::Match($mail,'token=([a-f0-9]{64})').Groups[1].Value
    AssertAlasTest ($resetToken.Length -eq 64) 'No se generaron instrucciones de recuperacion.'
    $page = PageAlasTest ('app/Views/restablecer.php?token=' + $resetToken) $publicAlasTest
    $null = PageAlasTest ('app/Views/restablecer.php?token=' + $resetToken) $publicAlasTest @{csrf_token=(TokenAlasTest $page);token=$resetToken;password='AlasNueva2026!';confirm_password='AlasNueva2026!'}
    $page = PageAlasTest ('app/Views/restablecer.php?token=' + $resetToken) $publicAlasTest
    AssertAlasTest ($page.Content.Contains('ya fue utilizado')) 'El enlace de recuperacion se puede reutilizar.'
    $page = PageAlasTest 'app/Views/login.php' $publicAlasTest
    $page = PageAlasTest 'app/Views/login.php' $publicAlasTest @{csrf_token=(TokenAlasTest $page);cedula=$cedulaAlasTest;password='AlasNueva2026!'}
    AssertAlasTest ($page.Content.Contains('Panel de administraci')) 'No funciona el acceso despues de recuperar.'
    $forbidden = Invoke-WebRequest ($baseAlasTest + 'app/Views/correos.php') -WebSession $publicAlasTest -SkipHttpErrorCheck -TimeoutSec 15
    AssertAlasTest ($forbidden.StatusCode -eq 403) 'Un empleado accedio a correos privados.'
    Write-Output 'OK solicitud pendiente, aprobacion, recuperacion de un uso y permisos.'
    for ($intentoAcceso = 1; $intentoAcceso -le 6; $intentoAcceso++) {
        $sesionIntento = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
        $pageIntento = PageAlasTest 'app/Views/login.php' $sesionIntento
        $null = PageAlasTest 'app/Views/login.php' $sesionIntento @{csrf_token=(TokenAlasTest $pageIntento);cedula=$cedulaAlasTest;password='IncorrectaDePrueba'}
    }
    $sesionIntento = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
    $pageIntento = PageAlasTest 'app/Views/login.php' $sesionIntento
    $bloqueoPersistente = PageAlasTest 'app/Views/login.php' $sesionIntento @{csrf_token=(TokenAlasTest $pageIntento);cedula=$cedulaAlasTest;password='AlasNueva2026!'}
    AssertAlasTest ($bloqueoPersistente.Content.Contains('un minuto')) 'Cambiar de sesion permitio eludir el bloqueo de acceso.'
    $null = DbAlasTest "UPDATE alas_bd.intentos_acceso SET bloqueado_hasta=DATE_SUB(NOW(), INTERVAL 1 SECOND) WHERE cedula=$cedulaAlasTest"
    $pageIntento = PageAlasTest 'app/Views/login.php' $sesionIntento
    $accesoTrasVencimiento = PageAlasTest 'app/Views/login.php' $sesionIntento @{csrf_token=(TokenAlasTest $pageIntento);cedula=$cedulaAlasTest;password='AlasNueva2026!'}
    AssertAlasTest ($accesoTrasVencimiento.Content.Contains('Panel de administraci')) 'No se libero el acceso al vencer el bloqueo.'
    Write-Output 'OK versiones, pestanas independientes, carrito modificado y bloqueo entre sesiones.'
    $page = PageAlasTest ('app/Views/eliminar-producto.php?id=' + $idProductoAlasTest) $adminAlasTest
    $null = PageAlasTest ('app/Views/eliminar-producto.php?id=' + $idProductoAlasTest) $adminAlasTest @{csrf_token='incorrecto';id_producto=$idProductoAlasTest}
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.productos WHERE id_producto=$idProductoAlasTest AND eliminado_en IS NULL") -eq '1') 'No se rechazo un CSRF invalido.'
    $page = PageAlasTest ('app/Views/eliminar-producto.php?id=' + $idProductoAlasTest) $adminAlasTest
    $null = PageAlasTest ('app/Views/eliminar-producto.php?id=' + $idProductoAlasTest) $adminAlasTest @{csrf_token=(TokenAlasTest $page);id_producto=$idProductoAlasTest}
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.productos WHERE id_producto=$idProductoAlasTest AND eliminado_en IS NOT NULL") -eq '1') 'No se retiro del catalogo el producto.'
    AssertAlasTest ((DbAlasTest "SELECT COUNT(*) FROM alas_bd.pedido_detalles WHERE id_pedido=$idPedidoAlasTest") -eq '2') 'Se perdio el historial del pedido.'
    Write-Output 'OK CSRF, eliminacion del catalogo e historial conservado.'
    Write-Output 'VERIFICACION COMPLETA: todos los flujos probados.'
} finally {
    $null = DbAlasTest "DELETE FROM alas_bd.mensajes_contacto WHERE nombre='$tagAlasTest'; DELETE FROM alas_bd.correos_locales WHERE destinatario='$emailAlasTest'; DELETE FROM alas_bd.pedidos WHERE cliente_nombre IN ('$tagAlasTest','${tagAlasTest}PUBLICO','${tagAlasTest}OTRO'); DELETE FROM alas_bd.intentos_acceso WHERE cedula=$cedulaAlasTest;"
    if ($idUsuarioAlasTest -match '^\d+$') { $null = DbAlasTest "DELETE FROM alas_bd.recuperaciones WHERE id_usuario=$idUsuarioAlasTest; DELETE FROM alas_bd.solicitud_usuario WHERE id_sol=$idUsuarioAlasTest AND cedula=$cedulaAlasTest;" }
    if ($idProductoAlasTest -match '^\d+$') { $null = DbAlasTest "DELETE FROM alas_bd.productos WHERE id_producto=$idProductoAlasTest AND nombre='$tagAlasTest';" }
    if ($idProductoExtraAlasTest -match '^\d+$') { $null = DbAlasTest "DELETE FROM alas_bd.productos WHERE id_producto=$idProductoExtraAlasTest AND nombre='${tagAlasTest}SECUNDARIO';" }
    if ($archivoImagenAlasTest -match '^uploads/productos/[a-f0-9]+\.png$') {
        $path = [System.IO.Path]::GetFullPath((Join-Path (Join-Path $raizProyecto 'public') $archivoImagenAlasTest))
        if ($path.StartsWith([System.IO.Path]::GetFullPath((Join-Path $raizProyecto 'public/uploads/productos/')))) { Remove-Item -LiteralPath $path -ErrorAction Stop }
    }
}
