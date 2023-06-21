function redirect() {
  var dropdown = document.getElementById("websiteDropdown");
  var selectedValue = dropdown.value;

  if (selectedValue) {
    window.location.href = '/' + selectedValue;
  }
}

fetch('/getwebsites.php')
  .then(response => response.json())
  .then(data => {
    const dropdown = document.getElementById('websiteDropdown');

    var pattern = /archive\/.+\/.?\/(.+?\/)/;
    var url = window.location.href;

    var matches = url.match(pattern);
    var url = 'https://' + matches[1];

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