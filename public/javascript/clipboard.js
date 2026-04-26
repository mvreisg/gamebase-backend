const copy = () => {
  const button = document.getElementById("key-button");
  const pre = document.getElementById("key-text");
  if (button && pre) {
    button.addEventListener("click", async (event) => {
      try {
        await navigator.clipboard.writeText(pre.innerText);
        alert("Copied to clipboard!");
      } catch (e) {
        alert(e);
      }
    });
  }
};

export default {
  copy,
};
