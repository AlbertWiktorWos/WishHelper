<template>
  <div class="container py-5">
    <div class="row justify-content-center align-items-center">

      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h2 class="card-title mb-4 text-center">Log in</h2>

            <form :action="loginPath" method="post" @submit="handleSubmit" ref="formRef" novalidate>
              <input type="hidden" name="_csrf_token" :value="csrfToken">

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': v$.email.$error }"
                    id="email"
                    name="email"
                    v-model="form.email"
                    autocomplete="username"
                >
                <div class="invalid-feedback" v-for="err in v$.email.$errors" :key="err.$validator">
                  {{ err.$message }}
                </div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Hasło</label>
                <input
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': v$.password.$error }"
                    id="password"
                    name="password"
                    v-model="form.password"
                    autocomplete="current-password"
                >
                <div class="invalid-feedback" v-for="err in v$.password.$errors" :key="err.$validator">
                  {{ err.$message }}
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                  Log in
                </button>
              </div>
            </form>

            <!-- display errors from backend -->
            <div
                class="alert alert-danger mt-4 p-2 text-center"
                v-if="errorMessage"
            >
              {{ errorMessage }}
            </div>

            <div class="mt-3 text-center">
              <a href="/verify-email/resend">Resend verification email</a>
            </div>

          </div>
        </div>
      </div>

      <div class="col-md-6 d-none d-md-block text-end">
        <img :src="require('@images/auth/login.png')" alt="Login" class="img-fluid" />
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref } from 'vue'
import useVuelidate from '@vuelidate/core'
import {required, email, minLength, helpers} from '@vuelidate/validators'

const props = defineProps({
  csrfToken: { type: String, required: true },
  lastEmail: { type: String, default: '' },
  loginPath: { type: String, required: true },
  errors: { type: String, default: null },
})

const formRef = ref(null)

const form = reactive({
  email: props.lastEmail || '',
  password: '',
})

const rules = {
  email: {
    required: helpers.withMessage('The email is required', required),
    email
  },
  password: {
    required: helpers.withMessage('The password is required', required),
    minLength: minLength(8)
  },
}

const v$ = useVuelidate(rules, form)

const errorMessage = computed(() => props.errors)

const handleSubmit = async (e) => {
  e.preventDefault()
  const isValid = await v$.value.$validate()

  if (!isValid) {
    return
  }

  formRef.value.submit()
}
</script>
