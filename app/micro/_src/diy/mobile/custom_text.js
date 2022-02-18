/**
 * 自定义文本组件
 */
$(function(){
	//初始化组件索引
	var object_index;
	//初始化组件类型
	var object_type;
	
	//点击显示控制区
	$('.page-diy-section').on("click",'.object-item[data-type="custom_text"]',function(){
		//已经显示的不再触发
		$('.object-item').find('.diy-preview-controller').removeClass('show');
		$(this).find('.diy-preview-controller').addClass('show');
		
		object_index = $(this).data('object-index');
		object_type = $(this).data('type');
		
		//点击启用富文本编辑器按钮后续操作
		$('[data-object-index="'+object_index+'"]').on('click',['data-target="#customTextEditorModal"'],function(){
			//给保存按钮添加组件块索引
			$('#customTextEditorModal [data-rule="save"]').attr('data-object-index',object_index);

			//获取预览区内容
			var html = $('[data-object-index="'+object_index+'"]').find('.custom-text-preview .content').html();
			UE.getEditor('content').setContent(html);
			
			//将富文本编辑器内容写入预览区及控制区表单元素中
			$('button[data-object-index="'+object_index+'"]').click(function(e){
				//获取编辑器内容
				var html = UE.getEditor('content').getContent();
				//写入预览器
				$('[data-object-index="'+object_index+'"]').find('.custom-text-preview .content').html(html);
				//写入表单元素
				$('[data-object-index="'+object_index+'"] .diy-preview-controller').find('textarea[name="content"]').val(html);
				//关闭模态框
				$('#customTextEditorModal [data-dismiss="modal"]').click();
			});
		});
	});

	$('.page-diy-section .object-lists').on("click",'.btn-object[data-type="custom_text"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }

        let html = $('[data-object-type="'+type+'"]').html();
        //console.log(type);
        //console.log(html);
        $('.preview-target').append(html);
        //为新增元素添加编号索引，避免多次引入冲突
        let object_index='';

        $('.preview-target .object-item').each(function(index){
            var this_type = $(this).data('type');
            //为所有已显示组件元素DOM编号索引，避免多次引入冲突
            $(this).attr('data-object-index',this_type+'-'+index);
            object_index = this_type+'-'+index;
        });
    });

	
});