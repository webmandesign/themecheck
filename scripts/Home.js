/* -------------  Bouton Select -------------------- */

$('#content').on('change','#file',function(){ 
			document.getElementById('container_file_submit').style.display = 'inline';
			document.getElementById('content_select').style.display = 'none';
			document.getElementById('selected_file').value = document.getElementById('file').value;
			document.getElementById('select_zip').className = 'submit_zip';
					});

/* ------------- Submit -------------------- */

$('#content').on('change','#new_file',function(){ 
						document.getElementById('selected_file').value = document.getElementById('new_file').value;
					});

/* ----------- Select -------------*/

$(document).ready(function() {
				enableSelectBoxes();
			});
			
function enableSelectBoxes(){
// All themes
	$('div.select_cms').each(function(){
		$(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
		$(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));
		
		$(this).children('span.selected,span.selectArrow').click(function(){
			if($(this).parent().children('div.selectOptions').css('display') == 'none'){
				$(this).parent().children('div.selectOptions').css('display','block');
			}
			else
			{
				$(this).parent().children('div.selectOptions').css('display','none');
			}
		});
		
		$(this).find('span.selectOption').click(function(){
			$(this).parent().css('display','none');
			$(this).closest('div.select_cms').attr('value',$(this).attr('value'));
			$(this).parent().siblings('span.selected').html($(this).html());
		});
	});	

// Recent themes
	$('div.select_first').each(function(){
		$(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
		$(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));
		
		$(this).children('span.selected,span.selectArrow').click(function(){
			if($(this).parent().children('div.selectOptions').css('display') == 'none'){
				$(this).parent().children('div.selectOptions').css('display','block');
			}
			else
			{
				$(this).parent().children('div.selectOptions').css('display','none');
			}
		});
		
		$(this).find('span.selectOption').click(function(){
			$(this).parent().css('display','none');
			$(this).closest('div.select_first').attr('value',$(this).attr('value'));
			$(this).parent().siblings('span.selected').html($(this).html());
		});
	});	

}

/* ---------- Checkbox -------------*/
function check(id)
{ 
    if(id['id'] == 'check_data')
    {
        if(document.getElementById('sprite_check').className == 'sprite check')
        {
            document.getElementById('sprite_check').className = 'sprite checked';
            $('#check_data').attr('checked', true);
        }
        else
        {
            document.getElementById('sprite_check').className = 'sprite check';
            $('#check_data').attr('checked', false);
        }

    }
}

