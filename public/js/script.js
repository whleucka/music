htmx.on('htmx:responseError', function (event) {
    console.log("Oh snap! Response error!", event.detail.xhr.status);
    switch (event.detail.xhr.status) {
        case 400:
            break;
        case 401:
            window.location.href = '/sign-in';
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
