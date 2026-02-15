<template>
  <svg viewBox="0 0 100 100" class="overflow-visible drop-shadow-[0_0_15px_rgba(255,255,255,0.4)]">
    
    <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="6"/>
    
    <g class="drone-propeller">
      <circle cx="50" cy="50" r="8" fill="white" fill-opacity="0.8" />
      
      <ellipse cx="50" cy="25" rx="6" ry="22" fill="white" fill-opacity="0.3" stroke="white" stroke-width="1" stroke-opacity="0.5" />
      
      <ellipse cx="50" cy="25" rx="6" ry="22" fill="white" fill-opacity="0.3" stroke="white" stroke-width="1" stroke-opacity="0.5" 
               transform="rotate(120, 50, 50)" />
               
      <ellipse cx="50" cy="25" rx="6" ry="22" fill="white" fill-opacity="0.3" stroke="white" stroke-width="1" stroke-opacity="0.5" 
               transform="rotate(240, 50, 50)" />
    </g>
  </svg>
</template>

<style scoped>
.drone-propeller {
  transform-origin: 50px 50px;
  /* Continuous linear spin, 2.5 seconds per revolution for a smooth "cruising" speed */
  animation: drone-spin 2.5s linear infinite;
}

@keyframes drone-spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
