document.addEventListener("DOMContentLoaded", () => {

  const btn = document.getElementById("toggleSidebar");
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("mainContent");

  if (!btn || !sidebar || !main) return;

  btn.addEventListener("click", () => {

    const collapsed = sidebar.classList.toggle("collapsed");

    if(collapsed){
      main.classList.add("expanded");
    }else{
      main.classList.remove("expanded");
    }

  });

});
