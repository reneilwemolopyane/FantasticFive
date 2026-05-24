# FantasticFive

#Traveller

traveller_dashboard.php
The main page a traveller sees after logging in. It displays a welcome message using the traveller's name from localStorage. It connects to the database and fetches the top 5 destinations and top 3 highest rated packages to display as cards. It also shows 4 stat cards (bookings, reviews, group trips, destinations) which load via API calls. Recent bookings are fetched and displayed in a table at the bottom.

browse_packages.php
Allows travellers to search and filter all available travel packages. It connects to the database and runs a dynamic SQL query based on whatever filters the traveller selects — destination (city or country), package type, price range and sort order. Results display as cards showing the package image, title, destination, duration, price, agency name and buttons to view details or book.

package_details.php
Shows the full details of a single package. It receives the PackageID from the URL and queries the database for everything related to that package — the package info, agency details, destinations, flights, accommodation, car rental, attractions, restaurants and reviews. Everything displays in a two column layout with a sticky booking card on the right.

browse_destinations.php
Displays all destinations stored in the database as cards. Each card shows the destination image, city, country, climate, number of attractions and number of restaurants. Clicking a card opens a popup modal showing all attractions with entry fees and all restaurants with cuisine type and average cost. There is also a live search bar that filters destination cards as you type.

group_trips.php
Displays all available group trips created by agencies. Each trip card shows the agency name, a progress bar of how many seats are filled vs the total limit, and a Join Trip button. The page checks via API which trips the traveller has already joined and marks those accordingly. Joining a trip calls the API which inserts into the Traveller_group_trip table.

my_bookings.php
Shows all packages the traveller has booked. It loads bookings via an API call and displays them as cards showing the package image, title, agency, dates, duration, destination and price. There are three tabs — All Bookings, Upcoming and Past Trips — which filter the bookings in JavaScript without reloading the page. Past trips show a Review button.

book_package.php
The booking page for a specific package. It receives the PackageID from the URL and fetches the package details from the database. The traveller's name, surname and email are pre-filled automatically from localStorage. The traveller selects the number of travellers and the total price updates live. Clicking Confirm Booking calls the API which inserts into the BOOKS table. A success modal appears on completion.

my_reviews.php
Allows travellers to write and view their reviews. The left side shows all reviews the traveller has previously written, fetched via API. The right side has a form where the traveller selects a package from a dropdown (only packages they have booked appear), selects a star rating using a clickable star selector and writes a comment. Submitting calls the API which inserts into the REVIEW table.

traveller_profile.php
Allows travellers to manage their account. It pre-fills all fields from localStorage. There are three sections — Personal Information (name, surname, email, phone), Traveller Details (nationality, gender, passport number, date of birth) and Change Password with a live strength indicator. Saving any section calls the API to update the database and also updates localStorage so changes reflect immediately. The logout button clears localStorage and redirects to the login page.

logout.php
Destroys the PHP session and clears the user data from localStorage, then redirects to the login page.