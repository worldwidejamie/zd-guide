import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RadioControl, RangeControl, ToggleControl, Spinner, Notice } from '@wordpress/components';
import { Fragment, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import './editor.scss';

const TAXONOMY_OPTIONS = [
	{ value: 'zd_category', label: __('Categories', 'zd-guide') },
	{ value: 'zd_section', label: __('Sections', 'zd-guide') },
];

const clampItems = (value) => {
	const number = Number(value) || 0;
	return Math.min(Math.max(number, 1), 50);
};

export default function Edit({ attributes, setAttributes }) {
	const { taxonomy, itemsToShow, showCounts, showDescriptions } = attributes;

	const query = useMemo(
		() => ({
			per_page: clampItems(itemsToShow),
			hide_empty: false,
			context: 'view',
		}),
		[itemsToShow]
	);

	const { terms, isResolving, error } = useSelect(
		(select) => {
			const coreStore = select('core');
			const records = coreStore.getEntityRecords('taxonomy', taxonomy, query);
			const resolving = coreStore.isResolving('getEntityRecords', ['taxonomy', taxonomy, query]);
			const resolutionsError = coreStore.getLastEntityRecordError?.('taxonomy', taxonomy, query);

			return {
				terms: records,
				isResolving: resolving,
				error: resolutionsError,
			};
		},
		[taxonomy, query]
	);

	const blockProps = useBlockProps({
		className: `zd-guide-taxonomy-block is-${taxonomy}`,
	});

	const renderTerms = () => {
		if (error) {
			return (
				<Notice status="error" isDismissible={false}>
					{__('Unable to load taxonomy terms. Please try again.', 'zd-guide')}
				</Notice>
			);
		}

		if (isResolving && !terms) {
			return <Spinner />;
		}

		if (!terms?.length) {
			return (
				<Notice status="info" isDismissible={false}>
					{taxonomy === 'zd_category'
						? __('No categories found. Sync your Zendesk categories to populate this block.', 'zd-guide')
						: __('No sections found. Sync your Zendesk sections to populate this block.', 'zd-guide')}
				</Notice>
			);
		}

		return (
			<ul className="zd-guide-taxonomy-list">
				{terms.map((term) => (
					<li key={term.id} className="zd-guide-taxonomy-item">
						<div className="zd-guide-taxonomy-header">
							<span className="zd-guide-taxonomy-name">{term.name}</span>
							{showCounts && (
								<span className="zd-guide-taxonomy-count" aria-label={__('Article count', 'zd-guide')}>
									{term.count}
								</span>
							)}
						</div>
						{showDescriptions && term.description && (
							<p className="zd-guide-taxonomy-description">{term.description}</p>
						)}
					</li>
				))}
			</ul>
		);
	};

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('Display Options', 'zd-guide')} initialOpen>
					<RadioControl
						label={__('Taxonomy', 'zd-guide')}
						help={__('Choose which Zendesk taxonomy to display.', 'zd-guide')}
						options={TAXONOMY_OPTIONS}
						selected={taxonomy}
						onChange={(value) => setAttributes({ taxonomy: value })}
					/>
					<RangeControl
						label={__('Items to display', 'zd-guide')}
						value={itemsToShow}
						min={1}
						max={50}
						onChange={(value) => setAttributes({ itemsToShow: clampItems(value) })}
					/>
					<ToggleControl
						label={__('Show article counts', 'zd-guide')}
						checked={showCounts}
						onChange={(value) => setAttributes({ showCounts: value })}
					/>
					<ToggleControl
						label={__('Show descriptions', 'zd-guide')}
						checked={showDescriptions}
						onChange={(value) => setAttributes({ showDescriptions: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>{renderTerms()}</div>
		</Fragment>
	);
}
