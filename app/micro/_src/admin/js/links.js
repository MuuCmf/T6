
/**
连接至JS

思路：
三种情况：1、内部列表类链接 2 内部详情类链接 3、外部链接（已剥离出去）
## 外部页
外部页参数：1链接标题 2链接URL

**/
$(function(){

	//初始化组件索引
	var object_index;
	//初始化链接内容区索引
	var link_index = 0;
	//初始化底部导航设置页链接区索引

    // 打开连接至按钮
    $('body').on('click','[data-target="#linkTypeModal"]',function(){
        object_index = $(this).parents('.object-item').attr('data-object-index');
        link_index = $('[data-object-index="'+object_index+'"]').find('[data-rule="object-controller-item"]').index($(this).closest('[data-rule="object-controller-item"]'));
        //console.log(object_index);
        //console.log(link_index);
        //赋值到模态框
        $('#linkTypeModal').find('#objectIndex').val(object_index);
        $('#linkTypeModal').find('#linkIndex').val(link_index);
    });

    // 打开连接至设置框
    $('body').on('click','[data-target="#linkToModal"]',function(){

        //关闭选择模态框
        $('#linkTypeModal [data-dismiss="modal"]').click();
    });

    
});