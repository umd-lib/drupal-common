// Used to query against Primo/Alma for availability

document.addEventListener("DOMContentLoaded", function() {
  primoAvailability();
});

async function primoAvailability () {
  var mms_ids = [];

  var availability = window.document.getElementsByClassName("primo-availability");

  for (var i = 0; i < availability.length; i++) {
    if (i in availability) {
      if ('mms' in availability[i].dataset) {
        mms_ids.push(availability[i].dataset.mms);
      }
    }
  }

  console.log(mms_ids);

  try {
    const response = await fetch("/api/textbooks", {
      body: JSON.stringify( mms_ids ),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      method: 'POST',
    });
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }
    const dat = await response.text();
    console.log(dat);
  } catch (e) {
    console.error(e);
  }
}
