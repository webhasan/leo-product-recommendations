const LoadingIcon = () => (
    <svg
      version="1.1"
      id="Layer_1"
      x="0px"
      y="0px"
      viewBox="0 0 100 100"
      enableBackground="new 0 0 100 100"
      width="80"
      height="80"
    >
      <rect
        fill="#0073aa"
        width="3"
        height="45.2018"
        transform="translate(0) rotate(180 3 50)"
      >
        <animate
          attributeName="height"
          attributeType="XML"
          dur="1s"
          values="30; 100; 30"
          repeatCount="indefinite"
        ></animate>
      </rect>
      <rect
        x="17"
        fill="#0073aa"
        width="3"
        height="31.2018"
        transform="translate(0) rotate(180 20 50)"
      >
        <animate
          attributeName="height"
          attributeType="XML"
          dur="1s"
          values="30; 100; 30"
          repeatCount="indefinite"
          begin="0.1s"
        ></animate>
      </rect>
      <rect
        x="40"
        fill="#0073aa"
        width="3"
        height="56.7982"
        transform="translate(0) rotate(180 40 50)"
      >
        <animate
          attributeName="height"
          attributeType="XML"
          dur="1s"
          values="30; 100; 30"
          repeatCount="indefinite"
          begin="0.3s"
        ></animate>
      </rect>
      <rect
        x="60"
        fill="#0073aa"
        width="3"
        height="84.7982"
        transform="translate(0) rotate(180 58 50)"
      >
        <animate
          attributeName="height"
          attributeType="XML"
          dur="1s"
          values="30; 100; 30"
          repeatCount="indefinite"
          begin="0.5s"
        ></animate>
      </rect>
      <rect
        x="80"
        fill="#0073aa"
        width="3"
        height="31.2018"
        transform="translate(0) rotate(180 76 50)"
      >
        <animate
          attributeName="height"
          attributeType="XML"
          dur="1s"
          values="30; 100; 30"
          repeatCount="indefinite"
          begin="0.1s"
        ></animate>
      </rect>
    </svg>
);

export default LoadingIcon;