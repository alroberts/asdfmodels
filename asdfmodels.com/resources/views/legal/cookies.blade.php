<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border-2 border-black shadow-lg p-8">
                <h1 class="text-4xl font-bold text-black mb-6">Cookie Policy</h1>
                <p class="text-gray-600 mb-8">Last updated: {{ date('F j, Y') }}</p>

                <div class="prose prose-lg max-w-none space-y-6">
                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">1. What Are Cookies?</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Cookies are small text files that are placed on your device (computer, tablet, or mobile) when you visit a website. They are widely used to make websites work more efficiently and provide information to website owners.
                        </p>
                        <p class="text-gray-700 leading-relaxed mt-4">
                            Cookies allow a website to recognize your device and store some information about your preferences or past actions. This helps improve your browsing experience and allows the website to provide personalized content.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">2. How We Use Cookies</h2>
                        <p class="text-gray-700 leading-relaxed">We use cookies for the following purposes:</p>
                        
                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">2.1 Essential Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility. You cannot opt-out of these cookies as they are essential for the website to work.
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-4 space-y-2 ml-4">
                            <li><strong>Session cookies:</strong> Maintain your login session while you browse the site</li>
                            <li><strong>Security cookies:</strong> Help protect against security threats and fraud</li>
                            <li><strong>CSRF tokens:</strong> Protect against cross-site request forgery attacks</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">2.2 Functional Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These cookies enable enhanced functionality and personalization. They remember your preferences and choices to provide a more personalized experience.
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-4 space-y-2 ml-4">
                            <li>Remember your language preferences</li>
                            <li>Store your display preferences</li>
                            <li>Remember your search filters and settings</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">2.3 Analytics Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously. This helps us improve the website's functionality and user experience.
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-4 space-y-2 ml-4">
                            <li>Track which pages are visited most frequently</li>
                            <li>Understand how users navigate through the site</li>
                            <li>Identify areas for improvement</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">2.4 Performance Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These cookies collect information about how you use our website, such as which pages you visit most often. This data helps us optimize our website's performance.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">3. Types of Cookies We Use</h2>
                        
                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">3.1 First-Party Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These are cookies set by our website directly. They are used to remember your preferences and provide essential functionality.
                        </p>

                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">3.2 Third-Party Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These are cookies set by third-party services that appear on our pages. We may use third-party services for analytics, content delivery, and other functions. These services may set their own cookies.
                        </p>
                        <p class="text-gray-700 leading-relaxed mt-4">
                            Examples of third-party services we may use:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-4 space-y-2 ml-4">
                            <li>Analytics services (to understand website usage)</li>
                            <li>Content delivery networks (to improve page load times)</li>
                            <li>Social media platforms (if integrated)</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">4. Cookie Duration</h2>
                        
                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">4.1 Session Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These cookies are temporary and are deleted when you close your browser. They are used to maintain your session while you browse the website.
                        </p>

                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">4.2 Persistent Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            These cookies remain on your device for a set period or until you delete them. They remember your preferences and settings for future visits.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">5. Managing Cookies</h2>
                        <p class="text-gray-700 leading-relaxed">
                            You have the right to accept or reject cookies. Most web browsers automatically accept cookies, but you can usually modify your browser settings to decline cookies if you prefer.
                        </p>
                        
                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">5.1 Browser Settings</h3>
                        <p class="text-gray-700 leading-relaxed">
                            You can control cookies through your browser settings. Here are links to instructions for popular browsers:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-4 space-y-2 ml-4">
                            <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" class="text-black underline hover:text-gray-600">Google Chrome</a></li>
                            <li><a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences" target="_blank" class="text-black underline hover:text-gray-600">Mozilla Firefox</a></li>
                            <li><a href="https://support.apple.com/guide/safari/manage-cookies-and-website-data-sfri11471/mac" target="_blank" class="text-black underline hover:text-gray-600">Safari</a></li>
                            <li><a href="https://support.microsoft.com/en-us/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" class="text-black underline hover:text-gray-600">Microsoft Edge</a></li>
                        </ul>

                        <h3 class="text-xl font-semibold text-black mt-6 mb-3">5.2 Impact of Disabling Cookies</h3>
                        <p class="text-gray-700 leading-relaxed">
                            Please note that disabling cookies may affect the functionality of our website. Some features may not work properly if cookies are disabled, including:
                        </p>
                        <ul class="list-disc list-inside text-gray-700 mt-4 space-y-2 ml-4">
                            <li>Maintaining your login session</li>
                            <li>Remembering your preferences</li>
                            <li>Accessing certain features and services</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">6. Specific Cookies We Use</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Below is a list of the main cookies we use and their purposes:
                        </p>
                        
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full border-2 border-black">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border-2 border-black px-4 py-2 text-left font-semibold">Cookie Name</th>
                                        <th class="border-2 border-black px-4 py-2 text-left font-semibold">Purpose</th>
                                        <th class="border-2 border-black px-4 py-2 text-left font-semibold">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border-2 border-black px-4 py-2">laravel_session</td>
                                        <td class="border-2 border-black px-4 py-2">Maintains your login session</td>
                                        <td class="border-2 border-black px-4 py-2">Session</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-4 py-2">XSRF-TOKEN</td>
                                        <td class="border-2 border-black px-4 py-2">Security token for form submissions</td>
                                        <td class="border-2 border-black px-4 py-2">Session</td>
                                    </tr>
                                    <tr>
                                        <td class="border-2 border-black px-4 py-2">cookie_consent</td>
                                        <td class="border-2 border-black px-4 py-2">Remembers your cookie preferences</td>
                                        <td class="border-2 border-black px-4 py-2">1 year</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">7. Updates to This Cookie Policy</h2>
                        <p class="text-gray-700 leading-relaxed">
                            We may update this Cookie Policy from time to time to reflect changes in our practices or for other operational, legal, or regulatory reasons. We will notify you of any material changes by posting the new Cookie Policy on this page and updating the "Last updated" date.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-black mt-8 mb-4">8. Contact Us</h2>
                        <p class="text-gray-700 leading-relaxed">
                            If you have questions about our use of cookies or this Cookie Policy, please contact us through the Platform's support system or at the contact information provided on our website.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

