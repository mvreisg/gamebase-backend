let button = document.getElementById("key-button");
let pre = document.getElementById("key-text");
button.addEventListener("click", async (event) => {
  try {
    await navigator.clipboard.writeText(pre.innerText);
    alert("Copied to clipboard!");
  } catch (e) {
    alert(e);
  }
});
