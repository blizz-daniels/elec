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
    representative: 'Representative',
    address: 'Address',
    state: 'State',
  };

  const fieldWeights = {
    polling_code: 5,
    polling_no: 4,
    name: 4,
    ward: 3,
    lga: 2.7,
    representative: 2.7,
    district: 2.2,
    address: 1.8,
    state: 0.8,
  };

  const abbreviations = {
    pri: 'primary',
    prim: 'primary',
    pry: 'primary',
    sch: 'school',
    schl: 'school',
    ctr: 'center',
    ctre: 'center',
    centre: 'center',
    comm: 'community',
    govt: 'government',
    gov: 'government',
  };

  const lowSignalWords = new Set([
    'and', 'at', 'center', 'community', 'hall', 'in', 'of', 'open', 'polling',
    'primary', 'school', 'space', 'the', 'unit', 'i', 'ii', 'iii',
  ]);

  const maximumResults = 100;

  const normalizeText = (value) => String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, ' ')
    .trim();

  const tokenize = (value) => normalizeText(value)
    .split(' ')
    .filter(Boolean)
    .map((word) => abbreviations[word] || word);

  const editDistance = (left, right) => {
    const previous = Array.from({ length: right.length + 1 }, (_, index) => index);

    for (let row = 1; row <= left.length; row += 1) {
      let diagonal = previous[0];
      previous[0] = row;

      for (let column = 1; column <= right.length; column += 1) {
        const above = previous[column];
        previous[column] = Math.min(
          previous[column] + 1,
          previous[column - 1] + 1,
          diagonal + (left[row - 1] === right[column - 1] ? 0 : 1),
        );
        diagonal = above;
      }
    }

    return previous[right.length];
  };

  const wordSimilarity = (term, candidate) => {
    if (term === candidate) {
      return 1;
    }

    if (term.length < 3 || candidate.length < 3) {
      return 0;
    }

    if (candidate.includes(term) || term.includes(candidate)) {
      const lengthRatio = Math.min(term.length, candidate.length) / Math.max(term.length, candidate.length);
      return 0.76 + (0.24 * lengthRatio);
    }

    const maximumLength = Math.max(term.length, candidate.length);
    const allowedEdits = Math.max(1, Math.floor(maximumLength * 0.28));
    if (Math.abs(term.length - candidate.length) > allowedEdits) {
      return 0;
    }

    const similarity = 1 - (editDistance(term, candidate) / maximumLength);
    return similarity >= 0.68 ? similarity : 0;
  };

  document.querySelectorAll('select[data-searchable-select]').forEach((select) => {
    if (select.dataset.searchableReady === '1') {
      return;
    }

    const searchFields = (select.dataset.searchableFields || 'name')
      .split(',')
      .map((field) => field.trim())
      .filter(Boolean);
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
      const values = {
        name: option.dataset.searchName || option.textContent,
        polling_no: option.dataset.searchPollingNo || '',
        polling_code: option.dataset.searchPollingCode || '',
        ward: option.dataset.searchWard || '',
        lga: option.dataset.searchLga || '',
        district: option.dataset.searchDistrict || '',
        representative: option.dataset.searchRepresentative || '',
        address: option.dataset.searchAddress || '',
        state: option.dataset.searchState || '',
      };

      return values[field] || '';
    };

    const entries = Array.from(select.options)
      .slice(1)
      .map((option, originalIndex) => ({
        option,
        originalIndex,
        fields: Object.fromEntries(searchFields.map((field) => {
          const tokens = tokenize(getFieldValue(option, field));
          return [field, {
            tokens,
            compact: tokens.join(''),
          }];
        })),
      }));

    const scoreEntry = (entry, queryTokens, enabledFields) => {
      let score = 0;
      let matchedTerms = 0;
      const fieldHits = {};

      queryTokens.forEach((term) => {
        let best = 0;
        let bestField = '';

        enabledFields.forEach((field) => {
          const fieldData = entry.fields[field];
          if (!fieldData || fieldData.tokens.length === 0) {
            return;
          }

          let similarity = fieldData.tokens.reduce(
            (highest, candidate) => Math.max(highest, wordSimilarity(term, candidate)),
            0,
          );

          if (term.length >= 4 && fieldData.compact.includes(term)) {
            similarity = Math.max(similarity, 0.96);
          }

          const weightedSimilarity = similarity * (fieldWeights[field] || 1);
          if (weightedSimilarity > best) {
            best = weightedSimilarity;
            bestField = field;
          }
        });

        if (best > 0) {
          matchedTerms += 1;
          const termWeight = lowSignalWords.has(term) ? 0.35 : 1;
          score += best * termWeight;
          fieldHits[bestField] = (fieldHits[bestField] || 0) + 1;
        }
      });

      const strongestFieldHits = Math.max(0, ...Object.values(fieldHits));
      if (strongestFieldHits > 1) {
        score += (strongestFieldHits - 1) * 0.35;
      }

      return { score, matchedTerms };
    };

    const filterOptions = () => {
      const queryTokens = tokenize(input.value);
      const enabledFields = activeFields.size > 0 ? Array.from(activeFields) : ['name'];
      const selectedEntry = entries.find((entry) => entry.option.selected);

      if (queryTokens.length === 0) {
        entries.forEach((entry) => {
          entry.option.hidden = false;
          select.appendChild(entry.option);
        });
        emptyState.hidden = true;
        return;
      }

      const ranked = entries
        .map((entry) => ({ entry, ...scoreEntry(entry, queryTokens, enabledFields) }))
        .filter(({ score }) => score > 0)
        .sort((left, right) => (
          right.score - left.score
          || right.matchedTerms - left.matchedTerms
          || left.entry.originalIndex - right.entry.originalIndex
        ));

      const visibleEntries = ranked.slice(0, maximumResults).map(({ entry }) => entry);
      if (selectedEntry && !visibleEntries.includes(selectedEntry)) {
        visibleEntries.push(selectedEntry);
      }
      const visibleSet = new Set(visibleEntries);

      entries.forEach((entry) => {
        entry.option.hidden = !visibleSet.has(entry);
      });
      visibleEntries.forEach((entry) => select.appendChild(entry.option));

      emptyState.hidden = ranked.length !== 0;
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