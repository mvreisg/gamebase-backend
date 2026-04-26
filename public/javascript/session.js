const getToken = () => {
  return localStorage.getItem("token");
};

const setToken = (token) => {
  console.log("Setting token:", token);
  localStorage.setItem("token", token);
};

const validate = async (host) => {
  const token = getToken();
  if (token === null) {
    return false;
  }
  const response = await fetch(`${host}/authentication/validate`, {
    method: "GET",
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
  if (response.status !== 200) {
    return false;
  }
  const data = await response.json();
  if (data.status === "valid") {
    return true;
  }
  return false;
};

const login = async (user, host) => {
  const response = await fetch(`${host}/session/login`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      username: user.username,
      password: user.password,
      one_week_login: false,
    }),
  });
  if (response.status !== 201) {
    return {
      status: "failed",
    };
  }
  const { data } = await response.json();
  setToken(data.token);
  return {
    status: "success",
  };
};

export default {
  login,
  validate,
};
