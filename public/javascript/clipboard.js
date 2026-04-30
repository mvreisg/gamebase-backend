const copy = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch (e) {
    console.log(e);
    return false;
  }
};

export default {
  copy,
};
