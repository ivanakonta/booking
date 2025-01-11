// /* MODAL OPEN */
// const modal = document.getElementById("modal");
// const btn = document.getElementById("openBtn");
// const closeBtn = document.getElementById("closeBtn");

// btn.addEventListener("click", () => {
//   modal.showModal();
// });

// closeBtn.addEventListener("click", () => {
//   modal.close();
// });

// modal.addEventListener('cancel', () => {
//   modal.close();
// });


/* TABS */
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;

  tabcontent = document.getElementsByClassName('tabcontent');
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = 'none';

    tabcontent[i].style.borderTopLeftRadius = '10px';
  }

  tablinks = document.getElementsByClassName('tablinks');
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(' active', '');
  }

  document.getElementById(tabName).style.display = 'block';
  evt.currentTarget.className += ' active';


  if (evt.currentTarget === document.getElementById('defaultOpen')) {
    document.getElementById(tabName).style.borderTopLeftRadius = '0';
  }
}

document.getElementById('defaultOpen').click();

// function initTabs() {
//   document.getElementById('defaultOpen').click();
// }

// window.onload = initTabs;
