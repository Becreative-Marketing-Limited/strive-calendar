const { registerPlugin } = wp.plugins;
const { __ } = wp.i18n;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const el = wp.element.createElement;

const { PluginDocumentSettingPanel } = wp.editPost;
const { TextareaControl, PanelRow } = wp.components;

var PostNotesField = compose(
    withDispatch( function( dispatch, props ) {
        return {
            setMetaFieldValue: function( value ) {
                dispatch( 'core/editor' ).editPost(
                    { meta: { [ props.fieldName ]: value } }
                );
            }
        }
    } ),
    withSelect( function( select, props ) {
        return {
            metaFieldValue: select( 'core/editor' )
                .getEditedPostAttribute( 'meta' )
                [ props.fieldName ],
        }
    } )
)( function( props ) {
    return el( TextareaControl, {
        value: props.metaFieldValue,
        rows: 5,
        onChange: function( state ) {
            props.setMetaFieldValue( state );
        }
    } );
} );

const SCC_Post_Notes = () => (
    <PluginDocumentSettingPanel name="strive-post-notes" title={ __( 'Notes', 'strive') } icon="false">
        <PanelRow>
            <div class="strive-post-notes">
                <PostNotesField fieldName={'_strive_post_notes'} />
            </div>
        </PanelRow>
    </PluginDocumentSettingPanel>
);

registerPlugin( 'strive-post-notes', {
    render() {
        return(<SCC_Post_Notes />);
    }
} );