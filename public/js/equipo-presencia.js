(() => {
    const script = document.currentScript;
    const endpoint = script?.dataset.endpoint;
    if (!endpoint) return;

    window.setInterval(() => {
        fetch(endpoint, { credentials: 'same-origin', cache: 'no-store' }).catch(() => {});
    }, 30000);
})();

