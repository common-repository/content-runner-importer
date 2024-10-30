jQuery( function ( $ ) {
	
	$(function()
	{
		//when 'Select All Articles' is checked, check all the checkboxes
		$("#check-all").click(checkUncheckAll);
	});
	
	$(function()
	{
		//when the 'Custom Post Type' radio button is checked, show the post types dropdown
		$("#custom-radio-btn").on('click', function() {
			$("#cust-post-types-dropdown").removeClass("hidden");
		});
		
		//when the other radio buttons are checked, hide the post types dropdown
		$("#post-radio-btn, #page-radio-btn").on('click', function() {
			$("#cust-post-types-dropdown").addClass("hidden");
		});
		
		//to make the styled-select work
		$('body').on('click.select_open','.styled-select-top', function(event){
        	$('.styled-select').css('overflow','hidden');
        	event.stopPropagation()
        	$(this).closest('.styled-select').css('overflow','visible');
	    });
	    
	    $('body').on("click.select",'.styled-select li',function(){
	        var parentDiv=$(this).closest('.styled-select');
	        var displayText = $(this).text();
	        var liValue= $(this).data('val') !== undefined ? $(this).data('val') : displayText;
	        parentDiv.find('.styled-select-top').find('.text').text(displayText);
	        parentDiv.css('overflow','hidden');
	        parentDiv.find('input').val(liValue).trigger('change'); // trigger change event for benefit of other bindings
	    });
	    
	    // Off click of styled select will close all open styled selects
	    $('html').on('click',function(){
	        $('.styled-select').css('overflow','hidden');
	    });
	});
	
	function checkUncheckAll()
	{
		var checkboxes = $(".checkbox"); //get all checkboxes
		
		if(this.checked)
		{
			checkboxes.attr("checked", true);
			$(".details").fadeIn(400);
			$(".details").find("div").slideDown(300);
			$(".show-hide").css("background-position", "0px bottom");
			$(".show-hide-all").html("Hide All Articles");
		}
		else
		{
			checkboxes.removeAttr("checked");
		}
	}
	
	$(function()
	{
		//hide the sub-tables by default
		$(".details").hide();
		
		//when the plus-minus links are clicked
		$(".show-hide").click(showHideDetails);
		
		//when the show all/hide all table header link is clicked
		$(".show-hide-all").click(showHideAllDetails);
	});
	
	function showHideDetails()
	{
		var id = $(this).attr("href");
		
		if($(id).is(":visible"))
		{
			$(id).fadeOut(400);
			$(id).find("div").slideUp(300);
			$(this).css("background-position", "0px top");
			
			if ($(".details").is(":visible")) {
				$(".show-hide-all").html("Hide All Articles");
			} else {
				$(".show-hide-all").html("Show All Articles");
			}
		}
		else
		{
			$(id).fadeIn(400);
			$(id).find("div").slideDown(300);
			$(this).css("background-position", "0px bottom");
			
			if ($(".details").is(":visible")) {
				$(".show-hide-all").html("Hide All Articles");
			} else {
				$(".show-hide-all").html("Show All Articles");
			}
		}
		
		return false; //don't follow the link
	}
	
	function showHideAllDetails()
	{
		if ($(".details").is(":visible"))
		{
			$(".details").find("div").slideUp(300);
			$(".details").fadeOut(400);
			$(".show-hide").css("background-position", "0px top");
			$(".show-hide-all").html("Show All Articles");
		}
		else
		{
			$(".details").find("div").slideDown(300);
			$(".details").fadeIn(400);
			$(".show-hide").css("background-position", "0px bottom");
			$(".show-hide-all").html("Hide All Articles");
		}
		
		return false; //don't follow the link
	}
	
});