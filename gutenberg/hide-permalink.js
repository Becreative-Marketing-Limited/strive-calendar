/* This script is only loaded for Revisions */

const { subscribe } = wp.data; 

    //=======================================
    // Hide the Permalink section for Revisions
    //=======================================

    let doneHidingPermalink = false;

    function getPostData() {
        const postData = wp.data.select( 'core/editor' ).getCurrentPost();

        // If post is found and permalink section hasn't been checked yet
        if ( Object.keys(postData).length > 0 && !doneHidingPermalink ) {

            // Skip if there is no meta (can happen for CPTs that don't use custom fields)
            if ( postData.meta !== undefined ) {
                // If it's a revision, remove the Permalink section
                if ( postData.meta._strive_copy_of ) {
                    wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'post-link' );
                }
            }   
            // Stop trying no matter what after first check
            doneHidingPermalink = true;
        }
    }

    // Subscribe to run the check
    subscribe(getPostData);
