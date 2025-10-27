import { __ } from '@wordpress/i18n';
import{useCallback, useState} from'@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import {SelectControl, Spinner} from'@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({attributes, setAttributes}) {
	const blockProps = useBlockProps();
	const [forms, setForms] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);

	useEffect(() => {
		let mounted = true;

		apiFetch({path: '/wwj-zdguide/v1/ticket-forms'})
			.then(data => {
				if(!mounted) {
					return;
				}

				setForms(Array.isArray(data) ? data : []);
				setLoading(false);
			})
			.catch(err => {
				if(!mounted) {
					return;
				}

				setError(err?.message || __('Unable to load ticket forms.', 'wwj-zdguide'));
				setLoading(false);
			});

		return () => {
			mounted = false;
		};
	}, []);

	const options = [
		{label: __('Select a ticket form', 'wwj-zdguide'), value: 0},
		...forms.map(form => ({
			label: form.name,
			value: form.id
		}))
	];

	return(
		<div>
			{loading ? <Spinner/> : null}
			{error && <div className="wwj-zdguide-block-error">{error}</div>}
			{! loading && ! error && (
				<SelectControl
					labal={__('Ticket Form', 'wwj-zdguide')}
					value={attributes.selectedFormId}
					options={options}
					onChange={value => setAttributes({selectedFormId: Number(value)})}
				/>
			)}
		</div>
	)
}
