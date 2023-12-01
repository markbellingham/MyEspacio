class RequestHeaders {
    html()
    {
        const headers = new Headers();
        const token = $('#layout-token').value;
        headers.append('X-Layout', token);
        return headers;
    }

    json()
    {
        const headers = new Headers();
        headers.append('Accept', 'application/json');
        return headers;
    }

    jsonWithToken()
    {
        const headers = new Headers();
        const token = $('#layout-token').value;
        headers.append('X-Layout', token);
        headers.append('Accept', 'application/json');
        return headers;
    }
}

const requestHeaders = new RequestHeaders();
export default requestHeaders;