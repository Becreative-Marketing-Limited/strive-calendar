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
    // Active Checklist Dropdown
    //=======================================

    var activeChecklist = '';
    var ChecklistDropdown, AllChecklists;
    if (STRIVE.checklists !== false) {
        setActiveChecklist();
        addChecklistSelect();
        addChecklistItems();
        addPanels();
    } else {
        addEmptyPanel();
    }

    function setActiveChecklist(){
        // Check the post meta first
        activeChecklist = STRIVE.checklists_post_saved;
        // Reset if the checklist has been deleted
        var checklistDeleted = true;
        for (const entry of STRIVE.checklists) {
            if ( entry['id'] == STRIVE.checklists_post_saved ) {
                var checklistDeleted = false;
                break;
            }
        }
        if ( checklistDeleted ) {
            activeChecklist = '';
        }
        // Set to first checklist if there are any checked tasks (This is for sites migrating from < 1.17)
        if ( activeChecklist == '' && STRIVE.checklists_completed_tasks.length > 0 ) {
            activeChecklist = STRIVE.checklists[0]['id'];
        }
        // Fallback to global
        if ( activeChecklist == '' ) {
            activeChecklist = STRIVE.checklists_default_global;
        }
        // Use first checklist if neither set
        if ( activeChecklist == '' ) {
            activeChecklist = STRIVE.checklists[0]['id'];
        }
    }

    function addChecklistSelect() {
        var checklistOptions = [];

        for ( const key in STRIVE.checklists ) {
            checklistOptions.push({label: STRIVE.checklists[key]['title'], value: STRIVE.checklists[key]['id']});
        }
    
        // Create the dropdown
        ChecklistDropdown = compose(
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
                    // Use post meta when set, otherwise fallback activeChecklist
                    metaFieldValue: select( 'core/editor' )
                        .getEditedPostAttribute( 'meta' )
                        [ props.fieldName ] ? select( 'core/editor' )
                        .getEditedPostAttribute( 'meta' )
                        [ props.fieldName ] : activeChecklist
                }
            } )
        )( function( props ) {
            return el( Select, {
                label: 'Active Checklist',
                value: props.metaFieldValue || activeChecklist,
                options: checklistOptions,
                onChange: function( state ) {
                    props.setMetaFieldValue( state );
                    updateChecklistVisibility(state);
                    activeChecklist = state;
                }
            } );
        } );
    }

    function addChecklistItems() {

        // Checklist items from get_option() are stored here so they can be output in distinct panels
        AllChecklists = {
            'not-started': [],
            'writing': [],
            'editing': [],
            'complete': [],
        };

        // Loop through the checklist array
        for (const entry of STRIVE.checklists) {

            // Get the actual checklist
            const checklist = entry['checklist'];

            // Loop through the statuses in the checklist
            for ( const status in checklist ) {

                var checkboxes = [];

                // Loop through the tasks in the status
                for ( const key in checklist[status] ) {
                    let data = checklist[status][key].split('~');
                    let title = data[0];
                    let ID = data[1];

                    var checkBoxState = false;

                    // Set the checkbox as true/false
                    function setCheckboxState(select, checkBoxState) {
                        
                        // Get the current array of saved IDs (saved as String)
                        var idArray = select('core/editor').getEditedPostAttribute('meta')['_strive_checklists'];

                        // Parse the saved array
                        let parsed = JSON.parse(idArray);

                        // check if in array
                        if ( parsed.includes(ID) ) {
                            checkBoxState = true;
                        }
                        
                        return checkBoxState;
                    }

                    // Create checkbox element
                    let ChecklistCheckbox = (props) => {
                        return (
                            <CheckboxControl
                            label={ title }
                            checked={ props.state }
                            onChange={(value) => props.onCheckboxChange(value)}
                            />
                        )
                    }
                    ChecklistCheckbox = withSelect(
                        (select) => {
                            return {
                                state: setCheckboxState(select)
                            }
                        }
                    )(ChecklistCheckbox);

                    ChecklistCheckbox = withDispatch(
                        (dispatch) => {
                            return {
                                onCheckboxChange: (value) => {
                                    // Flip state to new state to update UI
                                    checkBoxState = !checkBoxState;
                                    
                                    // Get saved array (string) of IDs
                                    var idArray = wp.data.select('core/editor').getEditedPostAttribute('meta')['_strive_checklists'];

                                    // Parse the saved DB value
                                    var parsed = JSON.parse(idArray);

                                    // If empty (no completed steps), convert to an array
                                    if ( parsed == '' ) {
                                        parsed = [];
                                    }

                                    // If the box is now checked, add it's ID to the array. Otherwise, remove it
                                    if ( value ) {
                                        parsed.push(ID);
                                    } else {
                                        let index = parsed.indexOf(ID);
                                        parsed.splice(index, 1);
                                    }

                                    // Convert the array back into a string to save it
                                    let stringified = JSON.stringify(parsed);

                                    // Save the new array (string) of completed step IDs
                                    dispatch('core/editor').editPost({ meta: { _strive_checklists: stringified } });
                                }
                            }
                        }
                    )(ChecklistCheckbox);

                    // Add the checkbox into the appropriate status array
                    checkboxes.push(<ChecklistCheckbox />);
                }
                
                var classes = 'checkbox-container';
                
                // Make appropriate checklist items visible
                if ( activeChecklist == entry['id'] ) {
                    classes += ' show';
                }
                AllChecklists[status].push(<div data-id={entry['id']} class={classes} data-status={status}>{checkboxes}</div>);
            }
        }
    }

    function addPanels(){

        // Decide whether the panel should be opened already based on post's status
        var panelOpen = (panelSection) => {
            let status = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' )['_strive_editorial_status'];

            if ( panelSection == status ) {
                return true;
            } else {
                return false;
            }
        }

        // Register the sidebar
        registerPlugin( 'strive-checklists', {
            icon: 'editor-ul',
            render: () => {
                return (
                    <>
                    <PluginSidebarMoreMenuItem
                        target = 'strive-checklists'
                    >
                        {__('Post Checklist', 'strive')}
                    </PluginSidebarMoreMenuItem>
                    <PluginSidebar
                        name = 'strive-checklists'
                        title = {__('Post Checklist', 'strive')} 
                    >
                        <div id="checklist-select" class="checklist-select">
                            <ChecklistDropdown fieldName={'_strive_active_checklist'} />
                        </div>
                        <Panel>
                            <PanelBody title="Not Started" initialOpen={panelOpen('not-started')} onToggle={updateVisibleTasks} className="not-started">
                                {AllChecklists["not-started"]}
                            </PanelBody>
                            <PanelBody title="Writing" initialOpen={panelOpen('writing')} onToggle={updateVisibleTasks} className="writing">
                                {AllChecklists.writing}
                            </PanelBody>
                            <PanelBody title="Editing" initialOpen={panelOpen('editing')} onToggle={updateVisibleTasks} className="editing">
                                {AllChecklists.editing}
                            </PanelBody>
                            <PanelBody title="Complete" initialOpen={panelOpen('complete')} onToggle={updateVisibleTasks} className="complete">
                                {AllChecklists.complete}
                            </PanelBody>
                        </Panel>
                    </PluginSidebar>
                    </>
                )
            }
        });

        function updateChecklistVisibility(id) {
            document.querySelectorAll('.checkbox-container').forEach(function(container) {
                container.classList.remove('show');
                if ( container.dataset.id == id ) {
                    container.classList.add('show');
                }
            });
        }

        // Update visible tasks when panel is opened b/c dropdown may have changed since AllChecklists was built
        function updateVisibleTasks() {
            // Set timeout to avoid writing 100 lines to store and access my data... yup
            setTimeout(() => {updateChecklistVisibility(activeChecklist)}, 10);
        }
    }

    function addEmptyPanel(){
        registerPlugin( 'strive-checklists', {
            icon: 'editor-ul',
            render: () => {
                return (
                    <>
                    <PluginSidebarMoreMenuItem
                        target = 'strive-checklists'
                    >
                        {__('Post Checklist', 'strive')}
                    </PluginSidebarMoreMenuItem>
                    <PluginSidebar
                        name = 'strive-checklists'
                        title = {__('Post Checklist', 'strive')} 
                    >
                        <div class="no-checklists">
                            <p>You haven't created any checklists with Strive yet. Checklists can help you document and systemize your writing process.</p>
                            <p><a href={STRIVE.checklist_create_url} target="_blank" class="button-primary">Create a Checklist</a></p>
                        </div>
                    </PluginSidebar>
                    </>
                )
            }
        });
    }

} )( window.wp );