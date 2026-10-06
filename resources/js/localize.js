let translations = {};
let normalizedTranslations = new Map();

function installTranslations(catalog) {
    translations = catalog;
    normalizedTranslations = new Map(
        Object.entries(catalog).map(([source, target]) => [source.trim().toLocaleLowerCase(), target]),
    );
    localize(document.body);
    document.title = translateValue(document.title);
}

function translateValue(value) {
    const leading = value.match(/^\s*/)?.[0] ?? '';
    const trailing = value.match(/\s*$/)?.[0] ?? '';
    const source = value.trim();
    const translated = translations[source] ?? normalizedTranslations.get(source.toLocaleLowerCase());

    if (translated !== undefined) return `${leading}${translated}${trailing}`;

    const dynamicPrefix = Object.entries(translations)
        .filter(([prefix]) => prefix.endsWith(',') || prefix.endsWith(':') || prefix.endsWith(' '))
        .sort(([left], [right]) => right.length - left.length)
        .find(([prefix]) => source.toLocaleLowerCase().startsWith(prefix.toLocaleLowerCase()));

    if (dynamicPrefix) {
        const [prefix, localizedPrefix] = dynamicPrefix;
        const rest = source.slice(prefix.length);
        return `${leading}${localizedPrefix}${rest}${trailing}`;
    }

    return value;
}

function localize(root) {
    if (document.documentElement.lang !== 'sw' || !root) return;

    if (root.nodeType === Node.TEXT_NODE) {
        const translated = translateValue(root.nodeValue ?? '');
        if (translated !== root.nodeValue) root.nodeValue = translated;
        return;
    }

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
        acceptNode(node) {
            const parent = node.parentElement;
            if (!parent || parent.closest('script, style, textarea, code, pre, [data-no-translate]')) {
                return NodeFilter.FILTER_REJECT;
            }

            return NodeFilter.FILTER_ACCEPT;
        },
    });

    const textNodes = [];
    while (walker.nextNode()) textNodes.push(walker.currentNode);
    textNodes.forEach((node) => {
        const translated = translateValue(node.nodeValue ?? '');
        if (translated !== node.nodeValue) node.nodeValue = translated;
    });

    const elements = root.nodeType === Node.ELEMENT_NODE ? [root, ...root.querySelectorAll('*')] : root.querySelectorAll?.('*') ?? [];
    for (const element of elements) {
        if (element.matches('script, style, [data-no-translate]')) continue;
        for (const attribute of ['placeholder', 'title', 'aria-label', 'alt', 'value']) {
            if (attribute === 'value' && element.matches('input') && !['button', 'submit', 'reset'].includes(element.type)) continue;
            if (element.hasAttribute(attribute)) {
                element.setAttribute(attribute, translateValue(element.getAttribute(attribute)));
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new MutationObserver((changes) => {
        for (const change of changes) {
            if (change.type === 'childList') change.addedNodes.forEach((node) => localize(node));
            if (change.type === 'characterData' && change.target.parentElement) {
                const translated = translateValue(change.target.nodeValue ?? '');
                if (translated !== change.target.nodeValue) change.target.nodeValue = translated;
            }
        }
    }).observe(document.body, { childList: true, subtree: true, characterData: true });

    if (document.documentElement.lang === 'sw') {
        const catalogUrl = document.querySelector('[data-readarena-translations-url]')?.dataset.readarenaTranslationsUrl ?? '/translations/sw.json';
        fetch(catalogUrl, { headers: { Accept: 'application/json' } })
            .then((response) => response.ok ? response.json() : Promise.reject(new Error('Translation catalog unavailable')))
            .then(installTranslations)
            .catch(() => {});
    }
});
