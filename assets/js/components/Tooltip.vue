<template>
  <span ref="wrapper" class="tooltip-wrapper" @mouseenter="show" @mouseleave="hide">
    <slot></slot>
    <div
        v-if="visible"
        ref="tooltip"
        class="tooltip-box"
        :style="tooltipStyle"
    >
      {{ text }}
    </div>
  </span>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue'

const props = defineProps({
  text: { type: String, required: true },
  position: { type: String, default: 'top' }, // preferred position
  minWidth: { type: Number, default: 100 },  // min tooltip width in px
  maxWidth: { type: Number, default: 200 }   // max tooltip width in px
})

const wrapper = ref(null);
const tooltip = ref(null);
const visible = ref(false);
const tooltipStyle = ref({});

onMounted(async () => {
  visible.value = true;
  await nextTick();
  hide();
})

const show = async () => {
  visible.value = true;
  await nextTick();
  adjustPosition();
}

const hide = () => {
  visible.value = false;
  adjustPosition();
}

const adjustPosition = () => {
  if (!wrapper.value || !tooltip.value){
    return
  }

  const wrapperRect = wrapper.value.getBoundingClientRect();
  const tooltipRect = tooltip.value.getBoundingClientRect();
  const windowWidth = window.innerWidth;
  const windowHeight = window.innerHeight;

  let top, left;

  // horizontally - centered by default, but adjust if it goes off-screen
  left = wrapperRect.left + wrapperRect.width / 2 - tooltipRect.width / 2;
  if (left < 25){
    left = 25;
  }
  if (left + tooltipRect.width > windowWidth - 25) left = windowWidth - tooltipRect.width - 25;

  // vertical - top/bottom
  if (props.position === 'top') {
    top = wrapperRect.top - tooltipRect.height - 6;
    if (top < 25) top = wrapperRect.bottom + 6;
  } else {
    top = wrapperRect.bottom + 6;
    if (top + tooltipRect.height > windowHeight - 25) top = wrapperRect.top - tooltipRect.height - 6;
  }

  tooltipStyle.value = {
    position: 'fixed',
    left: `${left}px`,
    top: `${top}px`,
    zIndex: 9999,
    maxWidth: props.maxWidth+'px',
    whiteSpace: 'normal',
    minWidth: props.minWidth+'px'
  }
}
</script>


<style lang="scss" scoped>
@import '~styles/variables';

.tooltip-wrapper {
  position: relative;
  display: inline-block;
}

.tooltip-box {
  padding: 6px 10px;
  color: $wh-sage-100;
  background-color: $wh-sage-500;
  font-size: 0.85rem;
  border-radius: 4px;
  text-align: justify;
  white-space: normal;
  word-wrap: break-word;
  z-index: 1000;
  pointer-events: none;
  transition: opacity 1s;
}

</style>
