/**
 * Return true if `target` node is the same as or a child of `element`.
 *
 * @param {Node} target
 * @param {Node} element
 *
 * @return {boolean}
 */
const isSameOrChildOf = (target, element) => {
    while(null !== target.parentNode) {
        if (target === element) {
            return true
        }

        target = target.parentNode
    }

    return false
}

export default {
    isSameOrChildOf
}