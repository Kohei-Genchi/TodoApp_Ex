<template>
  <div class="fixed top-0 right-0 p-4 z-50">
    <div v-if="message" :class="[
            'p-4 rounded-md shadow-md transition-all duration-300 transform',
            isVisible ? 'translate-y-0 opacity-100' : '-translate-y-4 opacity-0',
            type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
        ]">
      {{ message }}
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  setup() {
    const message = ref('');
    const type = ref('success');
    const isVisible = ref(false);
    let timeout = null;

    // 通知を表示する関数
    function show(msg, msgType = 'success', duration = 3000) {
      // 既存のタイマーをクリア
      if (timeout) {
        clearTimeout(timeout);
      }

      // メッセージと種類を設定
      message.value = msg;
      type.value = msgType;

      // 表示状態に設定
      isVisible.value = true;

      // 一定時間後に非表示にする
      timeout = setTimeout(() => {
        isVisible.value = false;
        // アニメーションが終わった後にメッセージをクリア
        setTimeout(() => {
          message.value = '';
        }, 300);
      }, duration);
    }

    return {
      message,
      type,
      isVisible,
      show
    };
  }
};
</script>
