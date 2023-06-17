fetch('getwebsites.php')
  .then(response => response.json())
  .then(data => {
    // Get the dropdown element
    const dropdown = document.getElementById('websiteDropdown');

    // Iterate over the website data and create an option element for each website
    data.forEach(website => {
      const option = document.createElement('option');
      option.text = website.name;
      option.value = website.url;
      dropdown.appendChild(option);
    });
  })
  .catch(error => {
    console.error('Error:', error);
  });