const page = document.querySelectorAll('.section.page');
if (page.length) {
  ['first', 'last'].forEach(position => {
    // Select only the first level of elements.
    const elements = document.querySelectorAll('.alchemist.node.full > .layout > .layout__region > .exo-component-wrapper[data-component-' + position + ']');
    if (elements.length) {
      const id = elements[0].getAttribute('data-component-' + position).replace(/_/g, '-');
      page[0].classList.add('component-enabled');
      page[0].classList.add('component-' + position + '--' + id);
    }
  });
}
