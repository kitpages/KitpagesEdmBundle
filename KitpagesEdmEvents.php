<?php
namespace Kitpages\EdmBundle;

final class KitpagesEdmEvents
{
    const onDisplayNodeInTree = 'kitpages.edm.on_display_node_in_tree';
    const afterDisplayNodeInTree = 'kitpages.edm.after_display_node_in_tree';

    const onNewFileUpload = 'kitpages.edm.on_new_file_upload';
    const afterNewFileUpload = 'kitpages.edm.after_new_file_upload';

    const onNewVersionFileUpload = 'kitpages.edm.on_new_version_file_upload';
    const afterNewVersionFileUpload = 'kitpages.edm.after_new_version_file_upload';

    const onDeleteNode = 'kitpages.edm.on_delete_node';
    const afterDeleteNode = 'kitpages.edm.after_delete_node';

    const onModifyStatusNode = 'kitpages.edm.on_modify_status_node';
    const afterModifyStatusNode = 'kitpages.edm.after_modify_status_node';

    const afterCreateNodeDirectory = 'kitpages.edm.after_create_node_directory';
}
