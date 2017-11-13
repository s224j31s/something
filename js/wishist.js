var wish_url = "ajax/ajaxwishlist.php";
var login_url = "login2.php";
var spinner_image = "graphics/spinner.png";
$(document).ready(function(){
  //preload spinner image
  let img = document.createElement('img');
  img.setAttribute('src',spinner_image);
  
  //get total number of items in wishlist.php page
  if($('[data-wishlist-item]').length){
    var total_wish = $('[data-wishlist-item]').length;
    }
  else{
    var total_wish = parseInt($('.wish-count').text());
  }
  
  //delete an item from wishlist
  $('button[name="delete"]').click(function(event){
    event.preventDefault();
    let product_id = $(event.target).data("id");
    let list_id = $(event.target).data("list-id");
    let user_id = $(event.target).data("user-id");
    //create data object and populate with data
    let data_obj = new Object();
    data_obj.id = product_id;
    data_obj.action = 'delete';
    data_obj.listid = list_id;
    data_obj.userid = user_id;
    //make ajax request
    $.ajax({
      beforeSend:addSpinner(event.target),
      type:'post',
      url: wish_url,
      data: data_obj,
      dataType: 'json',
      encode: true 
    })
    .done(function(response){
      if(response.success == true){
        //remove the element that has been clicked
        total_wish=total_wish-1;
        updateWishCount(total_wish);
        let wishlist_item = '[data-wishlist-item="'+product_id+'"]';
        setTimeout(function(){
          $(wishlist_item).remove();
        },1500);
      }
    });
  });
  
  //Add an item to wishlist
  $('button[name="add"]').click(function(event){
    event.preventDefault();
    let product_id = $(event.target).data("id");
    let list_id = $(event.target).data("list-id");
    let user_id = $(event.target).data("user-id");
    //create data object and populate with data
    let data_obj = new Object();
    data_obj.id = product_id;
    data_obj.action = 'add';
    data_obj.listid = list_id;
    data_obj.userid = user_id;
    //make ajax request
    $.ajax({
      beforeSend:addSpinner(event.target),
      type:'post',
      url: wish_url,
      data: data_obj,
      dataType: 'json',
      encode: true 
    })
    .done(function(response){
      if(response.success == true){
        //remove the element that has been clicked
        total_wish=total_wish+1;
        console.log(total_wish);
        updateWishCount(total_wish);
        //remove spinner after 1.5 seconds
        setTimeout(function(){
          //remove the spinner
          removeSpinner(event.target);
          //add a check mark
          addCheck(event.target);
        },1500);
      }
      else{
        //adding item to wishlist has failed (because user is not logged in)
        //remove spinner
        removeSpinner(event.target);
        //take user to login page and send product id to login page via GET
        let redirect_url = login_url + "?" + "action=add&list=wish&productid=" + product_id;
        //redirect to login page
        //encode the URI to ensure it does not contain unfriendly characters
        window.location.href = encodeURI( redirect_url );
      }
    });
  });
});

function addSpinner(elm){
  //clone spinner from template on the page
  let sptemplate = $("#spinner-template").html();
  $(elm).append(sptemplate);
}

function removeSpinner(elm){
  //spinner element class is .spinner-container
  let spinner = $(elm).find('.spinner-container');
  spinner.remove();
}

function addCheck(elm){
  let check = $("#check-template").html();
  $(elm).append(check);
}

function updateWishCount(total){
  if(total !== 0){
    $('.wish-count').text(total);
  }
  else{
    $('.wish-count').empty();
  }
}