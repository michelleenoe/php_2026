export function hydratePostImages(root) {
  const scope = root || document;
  const images = scope.querySelectorAll('img[data-post-src]');

  images.forEach((el) => {
    if (el.getAttribute('data-post-hydrated') === '1') return;

    const realSrc = el.getAttribute('data-post-src');
    if (!realSrc) return;

    const placeholder = el.getAttribute('src') || '';
    el.setAttribute('data-post-placeholder', placeholder);
    el.loading = 'lazy';

    el.loading = 'lazy';
    el.setAttribute('data-post-hydrated', 'pending');

    const tmp = new Image();
    tmp.src = realSrc;
    tmp.onload = () => {
      if (document.body.contains(el)) {
        el.src = realSrc;
        el.setAttribute('data-post-hydrated', '1');
      }
    };
    tmp.onerror = () => {
      el.setAttribute('data-post-hydrated', '0');
      if (placeholder) el.src = placeholder;
    };
  });
}

document.addEventListener('DOMContentLoaded', () => {
  hydratePostImages();
});

window.hydratePostImages = hydratePostImages;
