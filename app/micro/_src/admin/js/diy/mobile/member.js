$(function(){
	/**
     * 加载数据
     * @param  {[type]} ){                     let type [description]
     * @return {[type]}     [description]
     */
    $('.page-diy-section .object-lists').on("click",'.btn-object[data-type="member"]',function(){

        let type = $(this).data('type');
        let open = $(this).data('open');
        //console.log(open);
        if(open == false) {
            toast.error('该组件完善中...','danger');
            return;
        }

        let html = $('[data-object-type="'+type+'"]').html();
        console.log(type);
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
})