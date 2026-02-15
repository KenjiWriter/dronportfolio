<template>
  <Teleport to="body">
    <div
      v-show="isVisible || isReturning"
      ref="droneRef"
      class="fixed z-[100] will-change-transform hover:scale-105"
      :class="[
        isDragging ? 'cursor-grabbing' : 'cursor-grab',
        (isVisible && !isReturning) ? 'pointer-events-auto' : 'pointer-events-none',
        (isDragging || isThrown) && !isReturning ? 'transition-none' : 
        isReturning ? 'transition-all duration-[600ms] ease-in' : 
        'transition-all duration-[2500ms] ease-in-out'
      ]"
      :style="{ 
          left: `${x}px`, 
          top: `${y}px`, 
          transform: `translate(-50%, -50%) rotate(${tilt}deg) scale(${scale * 0.8})`,
          opacity: (isVisible && !isReturning) ? 1 : 0
      }"
      @mousedown.prevent="startDrag"
    >
      <svg viewBox="0 0 200 200" class="w-24 h-24 md:w-32 md:h-32 drop-shadow-[0_20px_30px_rgba(0,0,0,0.4)] overflow-visible">
        
        <g stroke="rgba(255,255,255,0.8)" stroke-width="6" stroke-linecap="round">
            <line x1="70" y1="70" x2="30" y2="30" /> <line x1="130" y1="70" x2="170" y2="30" /> <line x1="70" y1="130" x2="30" y2="170" /> <line x1="130" y1="130" x2="170" y2="170" /> </g>

        <circle cx="100" cy="100" r="35" fill="rgba(255,255,255,0.1)" stroke="rgba(255,255,255,0.9)" stroke-width="4"/>
        <circle cx="100" cy="100" r="12" fill="rgba(255,255,255,0.8)" class="animate-pulse"/>

        <defs>
            <g id="hero-style-prop">
                 <circle cx="0" cy="0" r="28" fill="rgba(0,0,0,0.2)" stroke="rgba(255,255,255,0.9)" stroke-width="3"/>
                 <g class="drone-props-spin">
                    <circle cx="0" cy="0" r="6" fill="white" fill-opacity="0.8" />
                    <ellipse cx="0" cy="-15" rx="5" ry="18" fill="white" fill-opacity="0.6" />
                    <ellipse cx="0" cy="-15" rx="5" ry="18" fill="white" fill-opacity="0.6" transform="rotate(120)"/>
                    <ellipse cx="0" cy="-15" rx="5" ry="18" fill="white" fill-opacity="0.6" transform="rotate(240)"/>
                </g>
            </g>
        </defs>

        <use href="#hero-style-prop" x="30" y="30" />   <use href="#hero-style-prop" x="170" y="30" />  <use href="#hero-style-prop" x="30" y="170" />  <use href="#hero-style-prop" x="170" y="170" /> </svg>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const isReturning = ref(false);
const x = ref(-200);
const y = ref(-200);
const tilt = ref(0);
const scale = ref(0.1);
const droneRef = ref(null);

// Interactive State
const isDragging = ref(false);
const isThrown = ref(false);
let lastMouseX = 0;
let lastMouseY = 0;
let velocityX = 0;
let velocityY = 0;
const friction = 0.95;
let animationFrameId = null;

// Padding to keep drone fully on screen relative to its center
const dronePadding = 60; 

let roamInterval = null;
let mouseX = 0;
let mouseY = 0;
let isEvading = false;

// Helper to ensure coordinates stay within screen bounds
const clampToScreen = (targetX, targetY) => {
    const finalX = Math.max(dronePadding, Math.min(window.innerWidth - dronePadding, targetX));
    const finalY = Math.max(dronePadding, Math.min(window.innerHeight - dronePadding, targetY));
    return { x: finalX, y: finalY };
};

const setStartPosition = () => {
    const originElement = document.getElementById('hero-lens-origin');
    if (originElement) {
        const rect = originElement.getBoundingClientRect();
        x.value = rect.left + rect.width / 2;
        y.value = rect.top + rect.height / 2;
    }
};

const handleScroll = () => {
    const scrollThreshold = window.innerHeight * 0.6;
    const shouldBeVisible = window.scrollY > scrollThreshold;

    if (shouldBeVisible && !isVisible.value && !isReturning.value) {
        // TAKEOFF
        setStartPosition();
        isVisible.value = true;
        velocityX = 0; velocityY = 0; isDragging.value = false; isThrown.value = false;
        setTimeout(() => { scale.value = 1; roam(); }, 50);
    } else if (!shouldBeVisible && isVisible.value && !isReturning.value) {
        // DOCKING (Return to base)
        isReturning.value = true;
        
        // Disable physics/dragging
        isDragging.value = false;
        isThrown.value = false;
        isEvading = false;
        
        // Re-calculate where the 'O' is RIGHT NOW as the user scrolls
        setStartPosition();
        
        // Shrink and reset tilt to match the text
        scale.value = 0.1;
        tilt.value = 0;
        
        // Wait for the 600ms transition to finish before completely hiding
        setTimeout(() => {
            isVisible.value = false;
            isReturning.value = false;
        }, 600);
    }
};

// --- DRAG & THROW LOGIC ---
const startDrag = (e) => {
    if (!isVisible.value || isReturning.value) return;
    isDragging.value = true;
    isThrown.value = false;
    lastMouseX = e.clientX;
    lastMouseY = e.clientY;
    document.body.style.userSelect = 'none';
};

const onMouseMove = (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;

    if (isDragging.value) {
        velocityX = e.clientX - lastMouseX;
        velocityY = e.clientY - lastMouseY;
        lastMouseX = e.clientX;
        lastMouseY = e.clientY;

        const clamped = clampToScreen(e.clientX, e.clientY);
        x.value = clamped.x;
        y.value = clamped.y;
        tilt.value = velocityX * 2; 
    } else {
        if (!isThrown.value) checkProximity();
    }
};

const stopDrag = () => {
    if (!isDragging.value) return;
    isDragging.value = false;
    document.body.style.userSelect = '';

    if (Math.abs(velocityX) > 2 || Math.abs(velocityY) > 2) {
        isThrown.value = true;
        physicsLoop();
    }
};

// --- PHYSICS ENGINE ---
const physicsLoop = () => {
    if (!isThrown.value || isDragging.value) return;

    let nextX = x.value + velocityX;
    let nextY = y.value + velocityY;

    if (nextX < dronePadding || nextX > window.innerWidth - dronePadding) {
        velocityX = -velocityX * 0.7;
        nextX = x.value + velocityX;
    }
    if (nextY < dronePadding || nextY > window.innerHeight - dronePadding) {
        velocityY = -velocityY * 0.7;
        nextY = y.value + velocityY;
    }

    const clamped = clampToScreen(nextX, nextY);
    x.value = clamped.x;
    y.value = clamped.y;

    velocityX *= friction;
    velocityY *= friction;

    tilt.value = velocityX * 3;

    if (Math.abs(velocityX) < 0.5 && Math.abs(velocityY) < 0.5) {
        isThrown.value = false;
        velocityX = 0; velocityY = 0;
    } else {
        animationFrameId = requestAnimationFrame(physicsLoop);
    }
};

// --- AUTONOMOUS BEHAVIOR ---
const roam = () => {
    if (!isVisible.value || isEvading || isDragging.value || isThrown.value || isReturning.value) return;
    
    const targetX = dronePadding + Math.random() * (window.innerWidth - dronePadding * 2);
    const targetY = dronePadding + Math.random() * (window.innerHeight - dronePadding * 2);
    const clamped = clampToScreen(targetX, targetY);
    x.value = clamped.x;
    y.value = clamped.y;
    tilt.value = (Math.random() - 0.5) * 20;
};

const checkProximity = () => {
    if (!isVisible.value || !droneRef.value || isDragging.value || isReturning.value) return;

    const detectionRadius = 150; 
    const dx = x.value - mouseX;
    const dy = y.value - mouseY;
    const distance = Math.sqrt(dx * dx + dy * dy);

    if (distance < detectionRadius) {
        evade(dx, dy);
    } else {
        isEvading = false;
    }
};

const evade = (dx, dy) => {
    isEvading = true;
    const pushDistance = 200;
    let targetX = x.value + (dx / (Math.abs(dx) || 1)) * pushDistance;
    let targetY = y.value + (dy / (Math.abs(dy) || 1)) * pushDistance;
    targetX += (Math.random() - 0.5) * 50;
    targetY += (Math.random() - 0.5) * 50;
    
    const clamped = clampToScreen(targetX, targetY);
    x.value = clamped.x;
    y.value = clamped.y;
    tilt.value = dx > 0 ? -25 : 25;
};

onMounted(() => {
    setTimeout(setStartPosition, 500);
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', stopDrag);
    roamInterval = setInterval(roam, 4000); 
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', stopDrag);
    clearInterval(roamInterval);
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
});
</script>

<style scoped>
.drone-props-spin {
  animation: prop-spin 0.6s linear infinite;
  transform-origin: 0px 0px;
}

@keyframes prop-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
