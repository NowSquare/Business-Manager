@extends('../layouts.front')

@section('page_title', trans('g.terms_and_policy') . ' - ' . config('system.name'))
@section('page_description', trans('g.terms_and_policy'))

@section('content')

  <div class="my-3 my-md-5">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">
          {{ trans('g.terms_and_policy') }}
        </h1>
      </div>
      <div class="row">
        <div class="col-lg-3 order-lg-1 mb-4">
          <div class="list-group list-group-transparent mb-0">
            <a href="#1" class="list-group-item list-group-item-action">Introduction</a>
            <a href="#2" class="list-group-item list-group-item-action">Content</a>
            <a href="#3" class="list-group-item list-group-item-action">Accounts</a>
            <a href="#4" class="list-group-item list-group-item-action">Intellectual property</a>
            <a href="#5" class="list-group-item list-group-item-action">Links to other websites</a>
            <a href="#6" class="list-group-item list-group-item-action">Termination</a>
            <a href="#7" class="list-group-item list-group-item-action">Indemnification</a>
            <a href="#8" class="list-group-item list-group-item-action">Limitation of liability</a>
            <a href="#9" class="list-group-item list-group-item-action">What we do with your information</a>
            <a href="#10" class="list-group-item list-group-item-action">Information we collect</a>
          </div>
        </div>
        <div class="col-lg-9">
          <div class="card">
            <div class="card-body">
              <div class="text-wrap p-lg-6">
                <h2 class="mt-0 mb-4" id="1">Introduction</h2>

                <p class="lead">{{ config('system.legal_name') }} ("we" or "us") values its visitors' privacy. This privacy policy summarizes what information we might collect from a registered user or other visitor ("you"), and what we will and will not do with it.</p>
                <p class="lead">Please note that this privacy policy does not govern the collection and use of information by companies that {{ config('system.legal_name') }} does not control, nor by individuals not employed or managed by {{ config('system.legal_name') }}. If you visit a Web site that we mention or link to, be sure to review its privacy policy before providing the site with information.</p>

                <h2 class="mt-5 mb-4" id="2">Content</h2>
                <p class="lead">Our Service allows you to post, link, store, share and otherwise make available certain information, text, graphics, videos, or other material ("Content"). You are responsible for the Content that you post on or through the Service, including its legality, reliability, and appropriateness.</p>
                <p class="lead">By posting Content on or through the Service, You represent and warrant that: (i) the Content is yours (you own it) and/or you have the right to use it and the right to grant us the rights and license as provided in these Terms, and (ii) that the posting of your Content on or through the Service does not violate the privacy rights, publicity rights, copyrights, contract rights or any other rights of any person or entity. We reserve the right to terminate the account of anyone found to be infringing on a copyright.</p>
                <p class="lead">You retain any and all of your rights to any Content you submit, post or display on or through the Service and you are responsible for protecting those rights. We take no responsibility and assume no liability for Content you or any third party posts on or through the Service. However, by posting Content using the Service you grant us the right and license to use, modify, perform, display, reproduce, and distribute such Content on and through the Service.</p>
                <p class="lead">{{ config('system.legal_name') }} has the right but not the obligation to monitor and edit all Content provided by users.</p>
                <p class="lead">In addition, Content found on or through this Service are the property of {{ config('system.legal_name') }} or used with permission. You may not distribute, modify, transmit, reuse, download, repost, copy, or use said Content, whether in whole or in part, for commercial purposes or for personal gain, without express advance written permission from us.</p>

                <h2 class="mt-5 mb-4" id="3">Accounts</h2>
                <p class="lead">When you create an account with us, you guarantee that you are above the age of 18, and that the information you provide us is accurate, complete, and current at all times. Inaccurate, incomplete, or obsolete information may result in the immediate termination of your account on the Service.</p>
                <p class="lead">You are responsible for maintaining the confidentiality of your account and password, including but not limited to the restriction of access to your computer and/or account. You agree to accept responsibility for any and all activities or actions that occur under your account and/or password, whether your password is with our Service or a third-party service. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>
                <p class="lead">You may not use as a username the name of another person or entity or that is not lawfully available for use, a name or trademark that is subject to any rights of another person or entity other than you, without appropriate authorization. You may not use as a username any name that is offensive, vulgar or obscene.</p>

                <h2 class="mt-5 mb-4" id="4">Intellectual property</h2>
                <p class="lead">The Service and its original content (excluding Content provided by users), features and functionality are and will remain the exclusive property of {{ config('system.legal_name') }} and its licensors. The Service is protected by copyright, trademark, and other laws of both the United States and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of {{ config('system.legal_name') }}.</p>

                <h2 class="mt-5 mb-4" id="5">Links to other websites</h2>
                <p class="lead">Our Service may contain links to third party web sites or services that are not owned or controlled by {{ config('system.legal_name') }}.</p>
                <p class="lead">{{ config('system.legal_name') }} has no control over, and assumes no responsibility for the content, privacy policies, or practices of any third party web sites or services. We do not warrant the offerings of any of these entities/individuals or their websites.</p>
                <p class="lead">You acknowledge and agree that {{ config('system.legal_name') }} shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such content, goods or services available on or through any such third party web sites or services.</p>
                <p class="lead">We strongly advise you to read the terms and conditions and privacy policies of any third party web sites or services that you visit.</p>

                <h2 class="mt-5 mb-4" id="6">Termination</h2>
                <p class="lead">We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of the Terms.</p>
                <p class="lead">If you wish to terminate your account, you may simply discontinue using the Service.</p>
                <p class="lead">All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of liability.</p>

                <h2 class="mt-5 mb-4" id="7">Indemnification</h2>
                <p class="lead">You agree to defend, indemnify and hold harmless {{ config('system.legal_name') }} and its licensee and licensors, and their employees, contractors, agents, officers and directors, from and against any and all claims, damages, obligations, losses, liabilities, costs or debt, and expenses (including but not limited to attorney's fees), resulting from or arising out of a) your use and access of the Service, by you or any person using your account and password; b) a breach of these Terms, or c) Content posted on the Service.</p>

                <h2 class="mt-5 mb-4" id="8">Limitation of liability</h2>
                <p class="lead">In no event shall {{ config('system.legal_name') }}, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence) or any other legal theory, whether or not we have been informed of the possibility of such damage, and even if a remedy set forth herein is found to have failed of its essential purpose.</p>

                <h2 class="mt-5 mb-4" id="9">What we do with your personally identifiable information</h2>
                <p class="lead">It is always up to you whether to disclose personally identifiable information to us, although if you elect not to do so, we reserve the right not to register you as a user or provide you with any products or services. "Personally identifiable information" means information that can be used to identify you as an individual, such as, for example:</p>
                <ul class="lead">
                  <li>your name, company, email address, phone number, billing address, and shipping address</li>
                  <li>your {{ config('system.legal_name') }} user ID and password (if applicable)</li>
                  <li>any account-preference information you provide us</li>
                  <li>your computer's domain name and IP address, indicating<br>
                    where your computer is located on the Internet</li>
                  <li>session data for your login session, so that our computer can 'talk' to yours while you are logged in</li>
                </ul>
                <p class="lead">If you do provide personally identifiable information to us, either directly or through a reseller or other business partner, we will:</p>
                <ul class="lead">
                  <li>not sell or rent it to a third party without your permission — although unless you opt out (see below), we may use your contact information to provide you with information we believe you need to know or may find useful, such as (for example) news about our services and products and modifications to the Terms of Service;</li>
                  <li>take commercially reasonable precautions to protect the information from loss, misuse and unauthorized access, disclosure, alteration and destruction;</li>
                  <li>not use or disclose the information except:</li>
                  <ul class="lead">
                    <li>as necessary to provide services or products you have ordered, such as (for example) by providing it to a carrier to deliver products you have ordered;</li>
                    <li>in other ways described in this privacy policy or to which you have otherwise consented;</li>
                    <li>in the aggregate with other information in such a way so that your identity cannot reasonably be determined (for example, statistical compilations);</li>
                    <li>as required by law, for example, in response to a subpoena or search warrant;</li>
                    <li>to outside auditors who have agreed to keep the information confidential;</li>
                    <li>as necessary to enforce the Terms of Service;</li>
                    <li>as necessary to protect the rights, safety, or property of {{ config('system.legal_name') }}, its users, or others; this may include (for example) exchanging information with other organizations for fraud protection and/or risk reduction.</li>
                  </ul>
                </ul>

                <h2 class="mt-5 mb-4" id="10">Information we collect</h2>

                <h3 class="my-4">Strictly necessary cookies</h3>
                <p class="lead">{{ config('system.legal_name') }} uses "cookies" to store data on your computer. They do not store any personally identifiable information and cannot be switched off. We minimize the use of cookies, and the very few we use are essential to provide a good service.</p>

                <h3 class="my-4">Generic data and cookies</h3>
                <p class="lead">For {{ config('system.legal_name') }} user accounts we only store an email address for login purposes and a way to recover lost passwords. We will not sell or rent these email addresses to a third party. We use these email addresses to occasionally update users with {{ config('system.legal_name') }} related product and service information, or other important issues related to the service users registered for.</p>
                <p class="lead">Visitors can register and login on the {{ config('system.legal_name') }} website to use our free Nearby Notification service. In case <code>remember me</code> is checked when logging in, a cookie is set to automatically log you in the next time you visit our website.</p>

                <table class="table mt-5">
                 <tr>
                   <th>Cookie Name</th>
                   <th>Expiration Time</th>
                   <th>Description</th>
                 </tr>
                 <tr>
                   <td><code>XSRF-TOKEN</code></td>
                   <td><?php if (config('session.lifetime') < 60) { echo config('session.lifetime') . ' minutes'; } else { echo floor(config('session.lifetime') / 60) . ' hours'; } ?></td>
                   <td>Used to prevent cross site forgery attacks on forms. Doesn't contain any personally identifiable information.</td>
                 </tr>
                 <tr>
                   <td><code>{{ config('session.cookie') }}</code></td>
                   <td><?php if (config('session.lifetime') < 60) { echo config('session.lifetime') . ' minutes'; } else { echo floor(config('session.lifetime') / 60) . ' hours'; } ?></td>
                   <td>Cookie used to identify a session instance by ID. Doesn't store personally identifiable information, is to ensure the working of our website.</td>
                 </tr>
                </table>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@stop