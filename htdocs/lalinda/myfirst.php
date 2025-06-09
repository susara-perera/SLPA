<!DOCTYPE html>
<html>
<head>

	

<style>
   background-color: lightblue;
</style>

<script>
  function confirmRequest() {
  var r = confirm("Please confirm.");
  if (r == true) {
    location.href = 'https://www.slpa.lk/';
    //location.href ='@Url.Action("PreviewBills","Sites",new {id = Model.Id},null)';
  } else {
    //do nothing
  }

}
</script>

</head>
<body>

<div>
  <button type="button" onclick="confirmRequest();">
                Go to SLPA web
            </button>
</div>
</body>
</html>
