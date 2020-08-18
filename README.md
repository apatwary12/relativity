
<h1 id="relativity.php">relativity.php</h1>
<p>Google Analytics Post Relativity Plugin. Configured for Wordpress but can be reconfigured for any B2C php site. This code set assigns a relative score based on standard deviated statistics and allows you to organize your posts/ web-content in a more automated fashion.</p>
<p>Requirements:</p>
<blockquote>
<p>Composer<br>
Minimum PHP 7.2<br>
key.json file from Google API’s for Google Analytics</p>
</blockquote>
<h1 id="why">Why?</h1>
<p>When I first started working with large data sets and MLS feeds, I noticed hundreds and thousands of homes that were going unnoticed due to just website positioning, and going in and re-organizing and array of homes based on human intellect seemed redundant. So I set up 5 goals on Google Analytics to</p>
<ul>
<li>Interest/ Duration</li>
<li>Multiple Page views per session</li>
<li>Form Submit</li>
<li>Tel: click</li>
<li>CTA clicks</li>
</ul>
<p>I gave each of these goals a score weight on their importance to the websites initial goal and grabbed data on the entire site using the Google API and imported them into the site as a static file.</p>
<blockquote>
<p>google_api_pull.php &lt;-- All google API Logic<br>
google_api_pull.php &lt;-- All Scoring Logic</p>
</blockquote>
<p>Once the Scores are weighted and calculated from analytics, I identify each node by their Post ID and updated their meta using a URL reverse search on the Wordpress Database.</p>
<blockquote>
<p>update_analytics.php</p>
</blockquote>
<p>Finally in the dashboard i assign each node with their correct score which then can be used in multiple uses of business logic.</p>
<blockquote>
<p>relativity.php</p>
</blockquote>
<p>I’ve found sandwiching posts that are higher scored with a weaker scored will usually help the worst post gain traction. or instead if a post is unsaturated i would write front end code to turn off certain scores.</p>
<p>Ultimately this logic allows B2C customers deliver content to users in a much more appropriate fashion that is less biased by ASC DSC standards but on weighted relativity and ultimately will gain SEO points for less bounce rates.</p>

