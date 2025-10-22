import { __ } from '@wordpress/i18n';
import { useCallback, useMemo, useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	Notice,
	PanelBody,
	RangeControl,
	TextControl,
	ToggleControl,
	Spinner,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import './editor.scss';

const SEARCH_ENDPOINT = '/wwj-zdguide/v1/search';

export default function Edit({ attributes, setAttributes }) {
	const { placeholder, resultsPerPage, showExcerpt } = attributes;
	const blockProps = useBlockProps({
		className: 'wwj-zdguide-help-center-search',
	});

	const [query, setQuery] = useState('');
	const [results, setResults] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	const debouncedSearch = useMemo(() => {
		let timeoutId;

		return (value) => {
			window.clearTimeout(timeoutId);
			timeoutId = window.setTimeout(async () => {
				if (! value) {
					setResults([]);
					return;
				}

				setIsLoading(true);
				setError(null);

				try {
					const response = await apiFetch({
						path: addQueryArgs(SEARCH_ENDPOINT, {
							q: value,
							per_page: resultsPerPage,
							show_excerpt: showExcerpt ? '1' : '0',
						}),
					});

					setResults(Array.isArray(response?.results) ? response.results : []);
				} catch (apiError) {
					setError(apiError?.message || __('Unable to complete search request.', 'wwj-zdguide'));
					setResults([]);
				} finally {
					setIsLoading(false);
				}
			}, 300);
		};
	}, [resultsPerPage, showExcerpt]);

	const onSearchChange = useCallback(
		(value) => {
			setQuery(value);
			debouncedSearch(value);
		},
		[debouncedSearch]
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Search Settings', 'wwj-zdguide')} initialOpen>
					<TextControl
						label={__('Placeholder text', 'wwj-zdguide')}
						value={placeholder}
						onChange={(value) => setAttributes({ placeholder: value })}
					/>
					<RangeControl
						label={__('Results per search', 'wwj-zdguide')}
						value={resultsPerPage}
						min={1}
						max={10}
						onChange={(value) => setAttributes({ resultsPerPage: value })}
					/>
					<ToggleControl
						label={__('Show excerpts in results', 'wwj-zdguide')}
						checked={showExcerpt}
						onChange={(value) => setAttributes({ showExcerpt: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<label htmlFor="wwj-zdguide-help-center-search-input" className="screen-reader-text">
					{__('Search Zendesk articles', 'wwj-zdguide')}
				</label>
				<TextControl
					id="wwj-zdguide-help-center-search-input"
					placeholder={placeholder}
					value={query}
					type="search"
					onChange={onSearchChange}
				/>

				{isLoading && (
					<div className="wwj-zdguide-search__loading">
						<Spinner />
						<span>{__('Searching...', 'wwj-zdguide')}</span>
					</div>
				)}

				{error && (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				)}

				{! query && ! isLoading && ! error && (
					<div className="wwj-zdguide-search__empty-state">
						<p>{__('Start typing to search your synced Zendesk articles.', 'wwj-zdguide')}</p>
						<Button variant="secondary" onClick={() => onSearchChange('')}
							disabled>
							{__('Awaiting query', 'wwj-zdguide')}
						</Button>
					</div>
				)}

				{query && ! isLoading && ! error && (
					<ul className="wwj-zdguide-search__results">
						{results.length === 0 && (
							<li className="wwj-zdguide-search__result--empty">
								{__('No results found for your query.', 'wwj-zdguide')}
							</li>
						)}
						{results.map((result) => (
							<li key={result.id} className="wwj-zdguide-search__result">
								<strong>{result.title}</strong>
								{showExcerpt && result.excerpt && (
									<p>{result.excerpt}</p>
								)}
								{result.url && (
									<a href={result.url} target="_blank" rel="noopener noreferrer">
										{__('Open article', 'wwj-zdguide')}
									</a>
								)}
							</li>
						))}
					</ul>
				)}
			</div>
		</>
	);
}
