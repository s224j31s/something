$(document).ready(
  function(){
    $("#profile-image").on("change",onProfileChange);
  }    
);

function onProfileChange(profileevent){
  //clear all errors
  $("#profile-image").parents(".form-group").removeClass("has-error");
  $("#image-errors").empty();
  
  //get a reference to the file selected
  let files = profileevent.target.files;
  let imagefile = files[0];
  //get name of the file
  let imagename = imagefile.name;
  //get the size of the image in KB
  let imagesize = Math.ceil(imagefile.size/1024);
  
  //errors array to collect all errors
  let errors = [];
  //if image is larger than 5MB
  if(imagesize > 2*1024){
    let sizeerror = {name: "size", message: "Image is larger than 2MB, please use a smaller image"};
    errors.push(sizeerror);
  }
  
  //if(errors.length==0){
    //read the image using File API
    let reader = new FileReader();
    reader.addEventListener('load', function(event){
      let profileimg = event.target.result;
      //show profile image using an img element
      $("#profile-preview").attr("src", profileimg);
      //show information about image
      $("#profile-img-info").text(imagename+" "+imagesize+" KB");
      if(errors.length){
        //add error to form-group 
        $("#profile-image").parents(".form-group").addClass("has-error");
        //string error messages together
        let messages ="";
        for(let i=0;i < errors.length; i++){
          messages = messages + " " +errors[i].message;
        }
        $("#image-errors").text(messages);
        
        //disable update button
        $("#update-btn").attr("disabled","");
      }
      //if no errors
      else{
        $("#update-btn").removeAttr("disabled");
      }
    });
    
    reader.readAsDataURL(imagefile);
  //}
}