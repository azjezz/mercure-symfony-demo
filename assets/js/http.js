/**
 * construct a URL from the given URI.
 *
 * @param {String} uri
 * @return {URL}
 */
const url = (uri) => {
    if (uri.startsWith('https://') || uri.startsWith('http://')) {
        return new URL(uri);
    }

    let protocol = window.location.protocol;
    if (uri.startsWith('//')) {
        return new URL(`${protocol}${uri}`);
    }

    let host = window.location.host;
    if (uri.startsWith('/')) {
        return new URL(`${protocol}//${host}${uri}`);
    }

    let pathname = window.location.pathname;
    return new URL(`${protocol}//${host}${pathname}/${uri}`);
}

/**
 * Prefetch the given url.
 *
 * @param {URL} url
 * @returns {Promise<void>}
 */
const prefetch = async (url) => {
    let request = new Request(url.toString(),{
        method: 'GET',
        cache: 'force-cache',
    })

    await fetch(request)
}

export default {
    url,
    prefetch
}