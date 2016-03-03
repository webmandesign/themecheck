/* -------------  Bouton Select -------------------- */

$('#content').on('change','#file',function(){ 

		var nameUpload;

		if(navigator.userAgent.match(/chrome/i))
		{
            nameUpload = ($('#file').val()).substring(12,($('#file').val()).length);
            console.log('navigator->chrome');
        }
        else
        {
        	nameUpload = ($('#file').val());
        	console.log('autre navigator');
        }

    $('#container_file_submit').show();
    $('#content_select').hide();
    $('#selected_file').val(nameUpload);
    $('#select_zip').attr('class', 'submit_zip');
});



/*$('#content').on('change','#new_file',function(){ 


        var newUpload = $('#new_file').val();
        var nameNewUpload = newUpload.substring(12,newUpload.length);
       
        $('#selected_file').val(nameNewUpload);
   
        });*/

/* ----------- Select -------------*/

$(document).ready(function() {
				enableSelectBoxes();
			});
			
function enableSelectBoxes(){
// All themes
	$('div.select_cms').each(function(){

		if(sessionTheme == "")
		{
			$(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
			$(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));
		}
	
		
            //Open Options    
		$(this).children('span.selected,span.selectArrow').on('click', function(){
			if($(this).parent().children('div.selectOptions').css('display') == 'none'){
				$(this).parent().children('div.selectOptions').css('display','block');
			}
			else
			{
				$(this).parent().children('div.selectOptions').css('display','none');
			}
		});
		
            //Close Options and change value   
		$(this).find('span.selectOption').on('click', function(){
			$(this).parent().css('display','none');
			$(this).closest('div.select_cms').attr('value',$(this).attr('value'));
			$(this).parent().siblings('span.selected').html($(this).html());
		});
	});	

// Recent themes
	$('div.select_first').each(function(){

		if(sessionSort != 'Meilleurs scores en premier' && sessionSort != 'Higher scores first')
		{
			$(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
			$(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));
		}
		
		$(this).children('span.selected,span.selectArrow').on('click',function(){
			if($(this).parent().children('div.selectOptions').css('display') == 'none'){
				$(this).parent().children('div.selectOptions').css('display','block');
			}
			else
			{
				$(this).parent().children('div.selectOptions').css('display','none');
			}
		});
		
		$(this).find('span.selectOption').on('click',function(){
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

