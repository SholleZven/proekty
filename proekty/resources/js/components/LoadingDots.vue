<template>
  <span class="loading-dots">
    <span></span>
    <span></span>
    <span></span>
  </span>
</template>

<style scoped>
.loading-dots {
  display: inline-flex;
  gap: 4px;
  vertical-align: middle;
}

.loading-dots span {
  width: 6px;
  height: 6px;
  background-color: #000;
  border-radius: 50%;
  animation: blink 1.2s infinite ease-in-out both;
}

.loading-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.loading-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes blink {
  0%, 80%, 100% {
    opacity: 0;
  }
  40% {
    opacity: 1;
  }
}
</style>
