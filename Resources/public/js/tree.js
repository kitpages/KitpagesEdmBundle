var nodeExpand = function (el) {
    el.parent().parent().nextAll('div.kit-edm-tree-state-node').show();
    el.parent().find('.kit-edm-tree-collapsed').show();
    el.parent().find('.kit-edm-tree-expanded').hide();
}

var nodeCollapse = function (el) {
    el.parent().parent().nextAll('div.kit-edm-tree-state-node').hide();
    el.parent().find('.kit-edm-tree-collapsed').hide();
    el.parent().find('.kit-edm-tree-expanded').show();
}

var treeExpandAll = function (tree) {
    tree.find('.kit-edm-tree-collapsed').show();
    tree.find('.kit-edm-tree-expanded').hide();
    tree.find('div.kit-edm-tree-state-node').show();
}
var treeCollapseAll = function (tree) {
    tree.find('.kit-edm-tree-collapsed').hide();
    tree.find('.kit-edm-tree-expanded').show();
    tree.find('div.kit-edm-tree-state-node').hide();
}
$(document).ready(function() {
    $('.kit-edm-tree-hide').hide();
    $('.kit-edm-tree-collapse-all').click(function(){
       treeCollapseAll($(this).parent());
    });
    $('.kit-edm-tree-expand-all').click(function(){
       treeExpandAll($(this).parent());
    });
    $('.kit-edm-tree').delegate('.kit-edm-tree-expanded', 'click', function(event){
        nodeExpand($(this));
    })
    $('.kit-edm-tree').delegate('.kit-edm-tree-collapsed', 'click', function(event){
        nodeCollapse($(this));
    })
});