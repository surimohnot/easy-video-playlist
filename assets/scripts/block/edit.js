const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	MediaUpload,
	PanelColorSettings
} = wp.editor;
const { apiFetch } = wp;
const {
	Dashicon,
	SelectControl,
	PanelBody,
	Button,
	Disabled,
	Placeholder,
	RangeControl,
	TextControl,
	TextareaControl,
	ToggleControl,
	Toolbar
} = wp.components;
const ServerSideRender = wp.serverSideRender;
const { BlockControls, InspectorControls } = wp.blockEditor

class PodcastPlayer extends Component {
	constructor() {
		super( ...arguments );

		let chkEditing = '' === this.props.attributes.playlist;

		this.state = {
			editing: chkEditing,
			listIndex: [],
		};

		this.fetching = false;
		this.onSubmitURL = this.onSubmitURL.bind( this );
	}

	apiDataFetch(data, path) {
		if (this.fetching) {
			setTimeout( this.apiDataFetch.bind(this, data, path), 200 );
			return;
		}
		this.fetching = true;
		apiFetch( {
			path: '/evp/v1/' + path,
		} )
		.then( ( items ) => {
			let itemsList = Object.keys(items);
			itemsList = itemsList.map(item => {
				return {
					label: items[item],
					value: item,
				};
			});
			this.setState({ [data]: itemsList });
			this.fetching = false;
		} )
		.catch( (error) => {
			this.setState({ [data]: [] });
			this.fetching = false;
			console.log(error);
		} );
	}

	componentDidMount() {
		this.apiDataFetch('listIndex', 'lIndex');
	}

	componentDidUpdate( prevProps ) {
		
	}

	onSubmitURL( event ) {
		event.preventDefault();
		const { playlist } = this.props.attributes;
		if ( playlist ) {
			this.setState( { editing: false } );
		}
	}

	render() {
		const {
			playlist
		} = this.props.attributes;
		const { editing, listIndex } = this.state;
		const { setAttributes } = this.props;

		if ( editing ) {
			return (
				<Fragment>
					<Placeholder
						icon="playlist-video"
						label="Playlist"
					>
						<form onSubmit={ this.onSubmitURL }>
							{
								!! (listIndex && Array.isArray( listIndex ) && listIndex.length) &&
								<div style={{ width : "100%" }}>
								<SelectControl
									value={ playlist }
									onChange={ ( value ) => setAttributes( { playlist: value } ) }
									options={ listIndex }
									style={{ maxWidth: "none" }}
								/>
								</div>
							}
							<Button type="submit" style={{ backgroundColor: "#f7f7f7" }}>
								{ __( 'Show Playlist', 'easy-video-playlist' ) }
							</Button>
						</form>
					</Placeholder>
				</Fragment>
			);
		}

		const toolbarControls = [
			{
				icon: 'edit',
				title: __( 'Edit Playlist', 'easy-video-playlist' ),
				onClick: () => this.setState( { editing: true } ),
			},
		];

		return (
			<Fragment>
				<BlockControls>
					<Toolbar controls={ toolbarControls } />
				</BlockControls>
				<InspectorControls>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						block="evp-block/evp-block"
						attributes={ this.props.attributes }
					/>
				</Disabled>
			</Fragment>
		);
	}
}

export default PodcastPlayer;
