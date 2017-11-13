var wish_url = "ajax/ajaxwishlist.php";

$(document).ready(
  function(){
    $('#login-form').on('submit',onLogin);
  }
);

function onLogin(submitevent){
  submitevent.preventDefault();
  $('.alert').remove();
  $('button[type="submit"]').attr("disabled","");
  //get data from target form
  let formdata = new FormData(submitevent.target);
  //create login data to send to server
  let logindata = {user: formdata.get('user'), password: formdata.get('password')};
  //send data to server via ajax request
  $.ajax({
    type: 'post',
    url: 'ajax/ajaxlogin.php',
    data: logindata,
    dataType: 'json',
    encode: true
  })
  .done( function(response){
    if(response.success == true){
      //login is successful
      //create alert success
      displayAlert("success","login successful");
      //check if there are any item to add to a list (wish list or shopping list)
      let accountid = response.userid;
      //productid was added by php from the GET request
      if(addItemsToList(productid,accountid)){
        console.log("success");
      }
      window.setTimeout(function(){window.location.href="account.php"},1500);
    }
    else{
      //login is unsuccessful
      displayAlert("warning","login unsuccessful");
      $('button[type="submit"]').removeAttr("disabled");
    }
  });
}

function displayAlert(type,message){
  let template = $("#login-template").html().trim();
  let clone = $(template);
  //add message
  $(clone).find('.message').text(message);
  
  if(type == "success"){
    $(clone).addClass('alert-success');
  }
  else{
    $(clone).addClass('alert-danger');
  }
  $('#login-form').append(clone);
}

function addItemsToList(productid,accountid){
  let dataobj = new Object();
  dataobj.id = productid;
  dataobj.userid = accountid;
  if(list == "wish"){
    //get the action mode
    switch(action){
      case "add":
        //make an ajax request to add item to wishlist
        dataobj.action = "add";
        if(makeRequest(dataobj)){
          return true;
        }
        else{
          return false;
        }
        break;
      default:
        break;
    }
  }
  else if(list == "shopping"){
    
  }
}

function makeRequest(dataobj){
  $.ajax({
    type:'post',
    url: wish_url,
    data: dataobj,
    dataType: 'json',
    encode: true 
  })
  .done(
    function(response){
      if(response.success){
        return true;
      }
      else{
        return false;
      }
    }  
  );
}