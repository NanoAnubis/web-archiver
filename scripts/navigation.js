function redirect() {
  // Get the selected option's value
  var dropdown = document.getElementById("websiteDropdown");
  var selectedValue = dropdown.value;

  // Redirect to the selected website
  if (selectedValue) {
    window.location.href = '/' + selectedValue;
  }
}

fetch('/getwebsites.php')
  .then(response => response.json())
  .then(data => {
    // Get the dropdown element
    const dropdown = document.getElementById('websiteDropdown');

    var pattern = /archive\/.+\/.?\/(.+?\/)/;
    var url = window.location.href;

    var matches = url.match(pattern);
    var url = 'https://' + matches[1];

    // Iterate over the website data and create an option element for each website
    data.forEach(website => {
      if(website.url == url){
        const option = document.createElement('option');
        option.text = website.date + ' on mode ' + website.mode;
        option.value = website.dir;
        dropdown.appendChild(option);
      }
    });
  })
  .catch(error => {
    console.error('Error:', error);
  });