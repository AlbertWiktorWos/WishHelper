<template>
  <div class="checkbox-wrapper-22">
    <label class="switch" :for="id">
      <input
          type="checkbox"
          :id="id"
          :checked="modelValue"
          :disabled="disabled"
          @change="onChange"
      />
      <div class="slider round"></div>
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  id: {
    type: String,
    default: () => `switch-${Math.random().toString(36).slice(2)}`
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const onChange = (e) => {
  emit('update:modelValue', e.target.checked)
}
</script>

<style scoped lang="scss">
@import '~styles/variables';


.checkbox-wrapper-22 .switch {
  display: inline-block;
  height: 34px;
  position: relative;
  width: 60px;
}

.checkbox-wrapper-22 .switch input {
  display:none;
}

.checkbox-wrapper-22 .slider {
  background-color: $wh-sage-100;
  bottom: 0;
  cursor: pointer;
  left: 0;
  position: absolute;
  right: 0;
  top: 0;
  transition: .4s;
}

.checkbox-wrapper-22 .slider:before {
  background-color: $wh-sage-300;
  bottom: 4px;
  content: "";
  height: 26px;
  left: 4px;
  position: absolute;
  transition: .4s;
  width: 26px;
}

.checkbox-wrapper-22 input:checked + .slider {
  background-color: $wh-sage-500;
}

.checkbox-wrapper-22 input:checked + .slider:before {
  transform: translateX(26px);
}

.checkbox-wrapper-22 .slider.round {
  border-radius: 34px;
}

.checkbox-wrapper-22 .slider.round:before {
  border-radius: 50%;
}

</style>
