document.addEventListener("DOMContentLoaded", function(event) {
  // Sidebar active
  const links = document.querySelectorAll('#sidebar-links .link');
      
  if (links.length) {
    links.forEach((link) => {
      link.addEventListener('click', (e) => {
        links.forEach((link) => {
            link.classList.remove('active');
        });
        e.preventDefault();
        link.classList.add('active');
      });
    });
  }

  links.forEach((link) => {
    if (link.href === window.location.href) {
        link.classList.add("active");
    }
  });

  sidebarVisibility();
});


const sidebarToggle = () => {
  const sidebar = document.querySelector("#nav-sidebar");
  sidebar.classList.toggle("hide");
}

function sidebarVisibility() {
  console.log("HIIIII")
  const sidebar = document.querySelector("#nav-sidebar");
  if (window.innerWidth < 500) {
    sidebar.classList.add('hide');
  } else {
    sidebar.classList.remove('hide');
  }
}
window.addEventListener('resize', sidebarVisibility);
