KitpagesEdmBundle
=================

This Symfony2 Bundle is Electronic Document Manager.

Actual state
============
Under development

Dependencies
============
- kitpagesFileSystemBundle
- KitpagesUtilBundle

add the following entries to the deps in the root of your project file:

    [KitpagesFileSystemBundle]
        git=http://github.com/kitpages/KitpagesFileSystemBundle.git
        target=Kitpages/FileSystemBundle
    [KitpagesUtilBundle]
        git=https://github.com/kitpages/KitpagesUtilBundle.git
        target=/bundles/Kitpages/UtilBundle

Add the following entries to your autoloader:
        $bundles = array(
        ...
            new Kitpages\FileSystemBundle\KitpagesFileSystemBundle(),
        );

Installation
============
You need to add the following lines in your deps :

    [EdmBundle]
        git=git://github.com/kitpages/KitpagesEdmBundle.git
        target=Kitpages/EdmBundle

AppKernel.php
        $bundles = array(
        ...
            new Kitpages\EdmBundle\KitpagesEdmBundle(),
        );

app/config/routing.yml
    KitpagesEdmBundle:
        resource: "@KitpagesEdmBundle/Resources/config/routing.yml"
        prefix:   /

Configuration example
=====================
The following configuration defines 2 filesystems :

interne and client are two edm distinct.

Let's see the configuration in config.yml

    kitpages_edm:
        tree_list:
            interne:
                kitpages_file_system_id: kitsiteTest
                tmp_dir: %kernel.root_dir%/data/tmp
                version_number_to_keep: 3
            client:
                kitpages_file_system_id: kitpagesAmazon
                tmp_dir: %kernel.root_dir%/data/tmp


Usage example
=============
    // get the edm manager
    $interneEdm = $this->get("kitpages_edm.tree.interne");
    $clientEdm = $this->get("kitpages_edm.tree.client");

Add the following code into the pages where the tree is displayed
    <link rel="stylesheet" href="{{ asset('bundles/kitpagesEdm/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('bundles/kitpagesEdm/css/popup.css') }}">
    <script type="text/javascript" src="{{ asset ("bundles/kitpagesEdm/js/popup.js") }}" ></script>
    <script type="text/javascript" src="{{ asset ("bundles/kitpagesEdm/js/tree.js") }}" ></script>


    {% render 'KitpagesEdmBundle:Tree:widgetNodeTree' with {
        'nodeId': node.node_id,
        'user': {'id': user.id, 'name': user.username, 'email': user.email},
        'actionList': listAction,
        'openTreeLevel': 0,
        'htmlTreeId': 'super_tree'
    } %}

treeId is optional and openTreeLevel is equal to 0 by default