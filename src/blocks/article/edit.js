import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, Placeholder, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import ServerSideRender from '@wordpress/server-side-render';
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param {Object}   props               Block properties.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to set block attributes.
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { articleId, showExcerpt, showMeta } = attributes;
	const blockProps = useBlockProps();

	// Fetch articles using WordPress data store
	const { articles, isLoading } = useSelect((select) => {
		const { getEntityRecords, isResolving } = select(coreStore);

		return {
			articles: getEntityRecords('postType', 'zd_article', {
				per_page: -1,
				status: 'publish',
				orderby: 'title',
				order: 'asc',
			}),
			isLoading: isResolving('getEntityRecords', [
				'postType',
				'zd_article',
				{ per_page: -1, status: 'publish' },
			]),
		};
	}, []);

	// Convert articles to options for SelectControl
	const articleOptions = [
		{ value: 0, label: __('Select an article...', 'wwj-zdguide') },
		...(articles || []).map((article) => ({
			value: article.id,
			label: article.title.rendered,
		})),
	];

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={__('Article Settings', 'wwj-zdguide')}>
					<SelectControl
						label={__('Select Article', 'wwj-zdguide')}
						value={articleId}
						options={articleOptions}
						onChange={(value) => setAttributes({ articleId: parseInt(value, 10) })}
						disabled={isLoading}
					/>
					<ToggleControl
						label={__('Show Excerpt', 'wwj-zdguide')}
						checked={showExcerpt}
						onChange={(value) => setAttributes({ showExcerpt: value })}
					/>
					<ToggleControl
						label={__('Show Meta Information', 'wwj-zdguide')}
						checked={showMeta}
						onChange={(value) => setAttributes({ showMeta: value })}
					/>
				</PanelBody>
			</InspectorControls>

			{isLoading && (
				<Placeholder>
					<Spinner />
					<span>{__('Loading articles...', 'wwj-zdguide')}</span>
				</Placeholder>
			)}

			{!isLoading && articleId === 0 && (
				<Placeholder
					icon="book-alt"
					label={__('Zendesk Article', 'wwj-zdguide')}
					instructions={__('Select an article from the block settings to display it here.', 'wwj-zdguide')}
				/>
			)}

			{!isLoading && articleId > 0 && (
				<ServerSideRender
					block="wwj-zdguide/article"
					attributes={attributes}
				/>
			)}
		</div>
	);
}
