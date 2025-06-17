htmx.on('htmx:responseError', function (event) {
    console.log("Oh snap! Response error!", event.detail.xhr.status);
    switch (event.detail.xhr.status) {
        case 400:
            break;
        case 403:
            break;
        case 404:
            break;
        case 500:
            document.querySelector("html").innerHTML = event.detail.xhr.response;
            break;
    }
});

const showDotMenu = (e) => {
    const button = e.currentTarget.querySelector(".dot-menu");
    button.click();
    button.setAttribute("data-bs-auto-close", "false");
    new bootstrap.Dropdown(button).show();
    button.removeAttribute("data-bs-auto-close");
    setTimeout(_ => {
        new bootstrap.Dropdown(button);
    }, 10);
}
