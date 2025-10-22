import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const DEFAULT_ENDPOINT = '/wwj-zdguide/v1/search';
const DEBOUNCE_DELAY = 300;

const debounce = (callback, delay = DEBOUNCE_DELAY) => {
	let timeoutId;

	return (...args) => {
		window.clearTimeout(timeoutId);
		timeoutId = window.setTimeout(() => {
			callback(...args);
		}, delay);
	};
};

const renderStatus = (container, message) => {
	container.innerHTML = '';
	const paragraph = document.createElement('p');
	paragraph.className = 'wwj-zdguide-search__status';
	paragraph.textContent = message;
	container.appendChild(paragraph);
};

const renderResults = (container, items, showExcerpt, openLabel) => {
	container.innerHTML = '';

	if (! items.length) {
		return false;
	}

	const list = document.createElement('ul');
	list.className = 'wwj-zdguide-search__list';

	items.forEach((item) => {
		const listItem = document.createElement('li');
		listItem.className = 'wwj-zdguide-search__item';

		const title = document.createElement('p');
		title.className = 'wwj-zdguide-search__title';
		title.textContent = item.title ?? '';
		listItem.appendChild(title);

		if (showExcerpt && item.excerpt) {
			const excerpt = document.createElement('p');
			excerpt.className = 'wwj-zdguide-search__excerpt';
			excerpt.textContent = item.excerpt;
			listItem.appendChild(excerpt);
		}

		if (item.url) {
			const link = document.createElement('a');
			link.className = 'wwj-zdguide-search__link';
			link.href = item.url;
			link.rel = 'noopener noreferrer';
			link.target = '_blank';
			link.textContent = openLabel;
			listItem.appendChild(link);
		}

		list.appendChild(listItem);
	});

	container.appendChild(list);
	return true;
};

const initSearchBlock = (root) => {
	if (! root || root.dataset.initialized === '1') {
		return;
	}

	const form = root; // Root element is the form itself.
	const input = root.querySelector('.wp-block-search__input');
	const resultsContainer = root.querySelector('.wwj-zdguide-search-results');

	if (! form || ! input || ! resultsContainer) {
		return;
	}

	const perPage = parseInt(root.dataset.resultsPerPage ?? '5', 10) || 5;
	const showExcerpt = root.dataset.showExcerpt === '1';
	const endpoint = root.dataset.endpoint || DEFAULT_ENDPOINT;
	const searchingLabel = resultsContainer.dataset.labelSearching || 'Searching…';
	const emptyLabel = resultsContainer.dataset.labelEmpty || 'No results found for your query.';
	const errorLabel = resultsContainer.dataset.labelError || 'Something went wrong. Please try again.';
	const openLabel = resultsContainer.dataset.labelOpen || 'Open article';

	let currentAbortController = null;

	const resetResults = () => {
		resultsContainer.hidden = true;
		resultsContainer.removeAttribute('aria-busy');
		resultsContainer.innerHTML = '';
		root.classList.remove('is-loading');
	};

	const performSearch = async (searchTerm) => {
		const trimmed = searchTerm.trim();

		if (! trimmed) {
			resetResults();
			return;
		}

		if (currentAbortController) {
			currentAbortController.abort();
		}

		currentAbortController = typeof AbortController !== 'undefined' ? new AbortController() : null;

		root.classList.add('is-loading');
		resultsContainer.hidden = false;
		resultsContainer.setAttribute('aria-busy', 'true');
		renderStatus(resultsContainer, searchingLabel);

		try {
			const queryArgs = {
				q: trimmed,
				per_page: perPage,
				show_excerpt: showExcerpt ? '1' : '0',
			};
			const requestUrl = addQueryArgs(endpoint, queryArgs);
			const isAbsolute = /^https?:/i.test(endpoint);
			const fetchArgs = {
				signal: currentAbortController?.signal,
			};
			if (isAbsolute) {
				fetchArgs.url = requestUrl;
			} else {
				fetchArgs.path = requestUrl;
			}
			const response = await apiFetch(fetchArgs);

			const items = Array.isArray(response?.results) ? response.results : [];

			if (! renderResults(resultsContainer, items, showExcerpt, openLabel)) {
				renderStatus(resultsContainer, emptyLabel);
			}
		} catch (error) {
			if (error && error.name === 'AbortError') {
				return;
			}
			renderStatus(resultsContainer, error?.message || errorLabel);
		} finally {
			resultsContainer.removeAttribute('aria-busy');
			root.classList.remove('is-loading');
		}
	};

	const debouncedSearch = debounce(performSearch);

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		performSearch(input.value || '');
	});

	input.addEventListener('input', (event) => {
		debouncedSearch(event.target.value || '');
	});

	root.dataset.initialized = '1';
};

const initializeAllBlocks = () => {
	document.querySelectorAll('.wp-block-wwj-zdguide-help-center-search').forEach((block) => {
		initSearchBlock(block);
	});
};

domReady(() => {
	initializeAllBlocks();

	if (typeof MutationObserver !== 'undefined') {
		const observer = new MutationObserver(initializeAllBlocks);
		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	}
});
