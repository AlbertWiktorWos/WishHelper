<template>
  <div class="container py-5">
    <div class="row justify-content-center align-items-center">

      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h2 class="card-title mb-4 text-center">Registration</h2>

            <form :action="registerPath" method="post" @submit="handleSubmit" ref="formRef" novalidate>
              <input type="hidden" name="_csrf_token" :value="csrfToken">

              <div class="mb-3">
                <label class="form-label">Nick Name</label>
                <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': v$.nickName.$error }"
                    name="nickName"
                    v-model="form.nickName"
                >
                <div class="invalid-feedback" v-for="err in v$.nickName.$errors" :key="err.$validator">
                  {{ err.$message }}
                </div>
              </div>

              <SearchComponent
                  type="country"
                  v-model="form.country"
                  label="Select country"
                  id="country"
              />

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': v$.email.$error }"
                    name="email"
                    v-model="form.email"
                >
                <div class="invalid-feedback" v-for="err in v$.email.$errors" :key="err.$validator">
                  {{ err.$message }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': v$.password.$error }"
                    name="password"
                    v-model="form.password"
                >
                <div class="invalid-feedback" v-for="err in v$.password.$errors" :key="err.$validator">
                  {{ err.$message }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Repeat password</label>
                <input
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': v$.passwordRepeat.$error }"
                    v-model="form.passwordRepeat"
                >
                <div class="invalid-feedback" v-for="err in v$.passwordRepeat.$errors" :key="err.$validator">
                  {{ err.$message }}
                </div>

              </div>

              <div class="form-check mb-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="agree"
                    v-model="form.agreeTerms"
                    :class="{ 'is-invalid': v$.agreeTerms.$error }"
                >
                <label class="form-check-label" for="agree">
                  I accept the terms
                </label>
                <div class="invalid-feedback" v-if="v$.agreeTerms.$error">
                  <div class="invalid-feedback" v-for="err in v$.agreeTerms.$errors" :key="err.$validator">
                    {{ err.$message }}
                  </div>
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                  Register
                </button>
              </div>
            </form>

            <!-- display errors from backend -->
            <div class="alert alert-danger mt-4 p-2 text-center" v-if="backendErrors">
              {{ backendErrors }}
            </div>

          </div>
        </div>
      </div>

      <div class="col-md-6 d-none d-md-block text-end">
        <img :src="require('@images/auth/register.png')" alt="Register" class="img-fluid" />
      </div>

    </div>
  </div>
</template>

<script setup>
import {reactive, ref, computed} from 'vue'
import useVuelidate from '@vuelidate/core'
import { required, email, minLength, sameAs, helpers } from '@vuelidate/validators'
import SearchComponent from "@js/components/search/SearchComponent.vue";

// defineProps to receive data from the server, such as CSRF token, registration path, and any errors that occurred during registration
const props = defineProps({
  csrfToken: { type: String, required: true },
  registerPath: { type: String, required: true },
  errors: { type: [String, Object], default: null },
})

const formRef = ref(null)

// The reactive form object that holds the values of the form fields
const form = reactive({
  email: '',
  password: '',
  passwordRepeat: '',
  agreeTerms: false,
  country: '',
})

// The validation rules for the form fields, using Vuelidate validators and custom error messages
const rules = () => ({
  nickName: {
    required: helpers.withMessage('The nick name is required', required),
  },
  email: {
    required: helpers.withMessage('The email is required', required),
    email
  },
  country: {
    required: helpers.withMessage('The country is required', required),
  },
  password: {
    required: helpers.withMessage('The password is required', required),
    minLength: minLength(8)
  },
  passwordRepeat: {
    minLength: minLength(8),
    sameAs: helpers.withMessage('The repeated password is not the same', sameAs(computed(() => form.password))) // for dynamic validation based on current password value we need to use computed!
  },
  agreeTerms: {
    required: helpers.withMessage('You must accept the non-existent regulations!', required),
    },
});

const v$ = useVuelidate(rules, form)

// This computed property processes backend errors and formats them for display
const backendErrors = computed(() => {

  debugger;
  if (!props.errors){
    return null;
  }
  let massage = '';
  if (props.errors && typeof props.errors === 'object') {
    const labels = {
      email: 'Email',
      password: 'Password',
      nickName: 'Nick name',
      country: 'Country'
    }
    for (const key in props.errors) {
      massage += labels[key] + ': ' + props.errors[key];
    }
    return massage
  }

  return 'An error occurred during registration.'
})

const handleSubmit = async (e) => {
  debugger;
  e.preventDefault()
  const isValid = await v$.value.$validate()

  if (!isValid) {
    return
  }

  formRef.value.submit()
}
</script>
