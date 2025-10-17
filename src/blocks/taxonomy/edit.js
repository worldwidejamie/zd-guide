import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RadioControl, RangeControl, ToggleControl, Spinner, Notice } from '@wordpress/components';
import { Fragment, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import './editor.scss';

const TAXONOMY_OPTIONS = [
	{ value: 'zd_category', label: __('Categories', 'wwj-zdguide') },
	{ value: 'zd_section', label: __('Sections', 'wwj-zdguide') },
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
		className: `wwj-zdguide-taxonomy-block is-${taxonomy}`,
	});

	const renderTerms = () => {
		if (error) {
			return (
				<Notice status="error" isDismissible={false}>
					{__('Unable to load taxonomy terms. Please try again.', 'wwj-zdguide')}
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
						? __('No categories found. Sync your Zendesk categories to populate this block.', 'wwj-zdguide')
						: __('No sections found. Sync your Zendesk sections to populate this block.', 'wwj-zdguide')}
				</Notice>
			);
		}

		return (
			<ul className="wwj-zdguide-taxonomy-list">
				{terms.map((term) => (
					<li key={term.id} className="wwj-zdguide-taxonomy-item">
						<div className="wwj-zdguide-taxonomy-header">
							<span className="wwj-zdguide-taxonomy-name">{term.name}</span>
							{showCounts && (
								<span className="wwj-zdguide-taxonomy-count" aria-label={__('Article count', 'wwj-zdguide')}>
									{term.count}
								</span>
							)}
						</div>
						{showDescriptions && term.description && (
							<p className="wwj-zdguide-taxonomy-description">{term.description}</p>
						)}
					</li>
				))}
			</ul>
		);
	};

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('Display Options', 'wwj-zdguide')} initialOpen>
					<RadioControl
						label={__('Taxonomy', 'wwj-zdguide')}
						help={__('Choose which Zendesk taxonomy to display.', 'wwj-zdguide')}
						options={TAXONOMY_OPTIONS}
						selected={taxonomy}
						onChange={(value) => setAttributes({ taxonomy: value })}
					/>
					<RangeControl
						label={__('Items to display', 'wwj-zdguide')}
						value={itemsToShow}
						min={1}
						max={50}
						onChange={(value) => setAttributes({ itemsToShow: clampItems(value) })}
					/>
					<ToggleControl
						label={__('Show article counts', 'wwj-zdguide')}
						checked={showCounts}
						onChange={(value) => setAttributes({ showCounts: value })}
					/>
					<ToggleControl
						label={__('Show descriptions', 'wwj-zdguide')}
						checked={showDescriptions}
						onChange={(value) => setAttributes({ showDescriptions: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>{renderTerms()}</div>
		</Fragment>
	);
}
