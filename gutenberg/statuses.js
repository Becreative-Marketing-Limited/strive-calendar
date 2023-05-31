( function( wp ) {
    const registerPlugin = wp.plugins.registerPlugin;
    const { subscribe, withSelect, withDispatch } = wp.data; 
    const compose = wp.compose.compose;
    const el = wp.element.createElement;
    const Select = wp.components.SelectControl;
    const { PluginSidebar, PluginSidebarMoreMenuItem, PluginPostStatusInfo } = wp.editPost;
    const { __ } = wp.i18n;
    const { Panel, PanelBody, CheckboxControl } = wp.components;
    
    //=======================================
    // Editorial Status Dropdown
    //=======================================

    // Create editorial status dropdown
    var StatusDropdown = compose(
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
        return el( Select, {
            label: 'Editorial Status',
            value: props.metaFieldValue,
            options: [
                {label: 'Not Started', value: 'not-started'},
                {label: 'Writing', value: 'writing'},
                {label: 'Editing', value: 'editing'},
                {label: 'Complete', value: 'complete'}
            ],
            onChange: function( state ) {
                props.setMetaFieldValue( state );
            }
        } );
    } );

    // Add the status dropdown to the Status & Visibility section
    const PluginPostStatusInfoTest = () => (
        <PluginPostStatusInfo>
            <div class="editorial-status">
                <StatusDropdown fieldName={'_strive_editorial_status'} />
            </div>
        </PluginPostStatusInfo>
    );
     
    // Register the plugin so the dropdown is included
    registerPlugin( 'post-status-info-test', { render: PluginPostStatusInfoTest } );

} )( window.wp );