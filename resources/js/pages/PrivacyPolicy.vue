<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageTitle from '@/components/PageTitle.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })
</script>

<template>
    <div class="mx-auto not-pwa:max-w-5xl">
        <PageTitle>
            Privacy Policy
        </PageTitle>
        <div class="mt-2 mb-24 max-w-none prose prose-sm">
            <p class="text-sm text-muted-foreground">
                Updated: August 15, 2025
            </p>

            <p>Your privacy is important to us. This policy explains what information we collect and how we use it in connection with the Personal Book Library App (“the App”). By using the App, you agree to the practices described below.</p>

            <h2>1. Information We Collect</h2>
            <p>We collect the following types of data:</p>
            <ul>
                <li>
                    <strong>Account Information</strong><br>
                    When you create an account or log in, we may store your name, email address, and password (encrypted).
                </li>
                <li>
                    <strong>Book Data</strong><br>
                    The App stores details you enter about your books, notes, and reviews. This data is saved in our database to deliver the core functionality of the App.
                </li>
                <li>
                    <strong>Usage Data</strong><br>
                    We automatically log certain information about how you interact with the App (e.g., page visits, actions taken). This helps us improve features and diagnose issues.
                </li>
                <li>
                    <strong>Cookies and Similar Technologies</strong><br>
                    We may use cookies or local storage to remember your preferences and keep you logged in.
                </li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>We use collected data for the following purposes:</p>
            <ol>
                <li>
                    <strong>Providing and Maintaining the App</strong><br>
                    We store your account details and book information so you can manage your personal library.
                </li>
                <li>
                    <strong>Communication</strong><br>
                    If you opt in, we may send you updates or notifications about new features or changes to our policies.
                </li>
                <li>
                    <strong>Analytics and Improvements</strong><br>
                    Usage data helps us understand which features are most helpful and how we can improve the App.
                </li>
                <li>
                    <strong>Security</strong><br>
                    We monitor and log activity to detect and prevent fraudulent or unauthorized access.
                </li>
            </ol>

            <h2>3. Sharing Your Information</h2>
            <p>We do <strong>not</strong> sell your personal data. We only share information in limited circumstances:</p>
            <ul>
                <li>
                    <strong>Service Providers</strong><br>
                    We may use third-party services (e.g., hosting providers, analytics platforms) to operate the App. These providers only receive data necessary to perform their services.
                </li>
                <li>
                    <strong>Legal Compliance</strong><br>
                    We may disclose information if required by law or in response to valid requests by public authorities.
                </li>
            </ul>

            <h2>4. Data Retention</h2>
            <p>We retain your information for as long as your account is active or as needed to provide the App. You may request deletion of your account and associated data at any time by contacting us.</p>

            <h2>5. Your Rights</h2>
            <p>Depending on your jurisdiction, you may have certain rights regarding your personal data:</p>
            <ul>
                <li>The right to access or obtain a copy of your information</li>
                <li>The right to correct inaccurate data</li>
                <li>The right to request deletion of your data</li>
                <li>The right to withdraw consent for data processing (where applicable)</li>
            </ul>
            <p>To exercise any of these rights, please contact us using the information below.</p>

            <h2>6. Security</h2>
            <p>We take reasonable measures to safeguard your data, including encryption of sensitive information and regular security reviews. However, no method of transmission or storage is completely secure.</p>

            <h2>7. Children’s Privacy</h2>
            <p>The App is not intended for children under the age of 13. We do not knowingly collect personal data from children. If we become aware that a child has provided us with personal information, we will delete it promptly.</p>

            <h2>8. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. If significant changes are made, we will provide a prominent notice or send you an email.</p>

            <h2>9. Contact Us</h2>
            <p>If you have questions or requests regarding this Privacy Policy, please email us at <a :href="`mailto:support@spacemancodes.com`">support@spacemancodes.com</a>.</p>
        </div>
    </div>
</template>
