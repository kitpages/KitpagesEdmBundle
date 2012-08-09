var scrollPosition;
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
        $.ajax({
          url: $(this).attr('data-edm-tree-action-url'),
          success: function(){
          }
        });
    });
    $('.kit-edm-tree-expand-all').click(function(){
        treeExpandAll($(this).parent());
        $.ajax({
          url: $(this).attr('data-edm-tree-action-url'),
          success: function(){
          }
        });
    });
    $('.kit-edm-tree').delegate('.kit-edm-tree-expanded', 'click', function(event){
        nodeExpand($(this));
        $.ajax({
          url: $(this).attr('data-edm-tree-action-url'),
          success: function(){
          }
        });
        return false;
    })
    $('.kit-edm-tree').delegate('.kit-edm-tree-collapsed', 'click', function(event){
        nodeCollapse($(this));
        $.ajax({
          url: $(this).attr('data-edm-tree-action-url'),
          success: function(){
          }
        });
        return false;
    })
    $('.kit-edm-action-url').click(function(e) {
        $.ajax({
          url: $(this).attr('data-edm-tree-action-url'),
          success: function(){
          }
        });
    });
    $('.kit-edm-expand').click(function(e) {
        $(this).parent().parent().parent().find('.kit-edm-tree-expanded').trigger('click');
        e.preventDefault();
    });
    $(window).scrollTop(scrollPosition);
    var timer;
    $(window).scroll(function () {
        clearTimeout(timer);
        timer = setTimeout(
            function(){$.ajax({
                url: "{{ path('kitpages_edm_userpreference_tree_scroll')}}?scroll="+$(window).scrollTop()
            })},
            1000
        );
    });
});