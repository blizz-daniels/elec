document.addEventListener('DOMContentLoaded', () => {
  const toast = document.querySelector('[data-toast]');
  if (toast) {
    setTimeout(() => toast.classList.add('opacity-0'), 5000);
  }

  document.querySelectorAll('select[data-searchable-select]').forEach((select) => {
    if (select.dataset.searchableReady === '1') {
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select';

    const input = document.createElement('input');
    input.type = 'search';
    input.className = [
      'form-control',
      select.classList.contains('form-select-sm') ? 'form-control-sm' : '',
      'searchable-select__input',
    ].filter(Boolean).join(' ');
    input.autocomplete = 'off';
    input.spellcheck = false;
    input.placeholder = select.dataset.searchablePlaceholder || 'Type to search';
    input.setAttribute('aria-label', input.placeholder);

    const emptyState = document.createElement('div');
    emptyState.className = 'searchable-select__empty';
    emptyState.textContent = 'No matching polling units found.';
    emptyState.hidden = true;

    const filterOptions = () => {
      const term = input.value.trim().toLowerCase();
      let visibleCount = 0;

      Array.from(select.options).forEach((option, index) => {
        if (index === 0) {
          option.hidden = false;
          return;
        }

        const label = option.textContent.trim().toLowerCase();
        const matches = term === '' || label.includes(term);
        option.hidden = !matches;

        if (matches) {
          visibleCount += 1;
        }
      });

      emptyState.hidden = visibleCount !== 0;
    };

    const parent = select.parentNode;
    if (!parent) {
      return;
    }

    parent.insertBefore(wrapper, select);
    wrapper.appendChild(input);
    wrapper.appendChild(select);
    wrapper.appendChild(emptyState);

    select.dataset.searchableReady = '1';
    input.addEventListener('input', filterOptions);
    filterOptions();
  });
});
