document.addEventListener('DOMContentLoaded', () => {
  const toast = document.querySelector('[data-toast]');
  if (toast) {
    setTimeout(() => toast.classList.add('opacity-0'), 5000);
  }

  const fieldLabels = {
    name: 'Name',
    polling_no: 'Unit No',
    polling_code: 'Code',
    ward: 'Ward',
    lga: 'LGA',
    district: 'District',
  };

  document.querySelectorAll('select[data-searchable-select]').forEach((select) => {
    if (select.dataset.searchableReady === '1') {
      return;
    }

    const searchFields = (select.dataset.searchableFields || 'name').split(',').map((field) => field.trim()).filter(Boolean);
    const activeFields = new Set(searchFields);

    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select';

    const chips = document.createElement('div');
    chips.className = 'searchable-select__chips';
    if (searchFields.length > 1) {
      const allButton = document.createElement('button');
      allButton.type = 'button';
      allButton.className = 'searchable-select__chip is-active';
      allButton.textContent = 'All';
      allButton.dataset.field = 'all';
      chips.appendChild(allButton);

      searchFields.forEach((field) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'searchable-select__chip is-active';
        button.textContent = fieldLabels[field] || field;
        button.dataset.field = field;
        chips.appendChild(button);
      });
    }

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

    const getFieldValue = (option, field) => {
      const map = {
        name: option.dataset.searchName || option.textContent,
        polling_no: option.dataset.searchPollingNo || '',
        polling_code: option.dataset.searchPollingCode || '',
        ward: option.dataset.searchWard || '',
        lga: option.dataset.searchLga || '',
        district: option.dataset.searchDistrict || '',
      };

      return (map[field] || '').toLowerCase();
    };

    const filterOptions = () => {
      const term = input.value.trim().toLowerCase();
      let visibleCount = 0;
      const enabledFields = activeFields.size > 0 ? Array.from(activeFields) : ['name'];

      Array.from(select.options).forEach((option, index) => {
        if (index === 0) {
          option.hidden = false;
          return;
        }

        const matches = term === '' || enabledFields.some((field) => getFieldValue(option, field).includes(term));
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
    wrapper.appendChild(chips);
    wrapper.appendChild(input);
    wrapper.appendChild(select);
    wrapper.appendChild(emptyState);

    select.dataset.searchableReady = '1';

    if (chips.children.length > 0) {
      chips.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-field]');
        if (!button) {
          return;
        }

        const field = button.dataset.field || '';
        if (field === 'all') {
          activeFields.clear();
          searchFields.forEach((item) => activeFields.add(item));
        } else if (activeFields.has(field)) {
          if (activeFields.size > 1) {
            activeFields.delete(field);
          }
        } else {
          activeFields.add(field);
        }

        Array.from(chips.querySelectorAll('button[data-field]')).forEach((chip) => {
          const chipField = chip.dataset.field || '';
          if (chipField === 'all') {
            chip.classList.toggle('is-active', activeFields.size === searchFields.length);
          } else {
            chip.classList.toggle('is-active', activeFields.has(chipField));
          }
        });

        filterOptions();
      });
    }

    input.addEventListener('input', filterOptions);
    filterOptions();
  });
});
