import 'alpinejs'

import http from './http'
import html from './html'

/**
 * Prefetch all links with `prefetch` class.
 */
window.addEventListener('load', () => {
    let links = document.querySelectorAll('a.prefetch')
    links.forEach((link) => {
        let url = http.url(link.getAttribute('href'))
        if (url.pathname === window.location.pathname) {
            return
        }

        if (url.hostname !== window.location.hostname) {
            return
        }

        link.addEventListener('mouseover', async () => {
            await http.prefetch(url)
        }, { once: true })
    })
})

window.addEventListener('click', (e) => {
    let menu = document.getElementById("nav-content")
    let button = document.getElementById("nav-toggle")
    if (null === menu || null === button) {
        return
    }

    let target = e.target
    if (!target instanceof Node) {
        return
    }

    if (!html.isSameOrChildOf(target, menu)) {
        if (html.isSameOrChildOf(target, button)) {
            if (menu.classList.contains("hidden")) {
                menu.classList.remove("hidden")
            } else {
                menu.classList.add("hidden")
            }
        } else {
            menu.classList.add("hidden")
        }
    }
})