const idToCheck = 176300;
const data = [
    { id: 9480, name: "Post-Conference Newsletter Package", quantity: 1, price: "2500" },
    { id: 9481, name: "Bag Inserts", quantity: 1, price: "110" },
    { id: 9479, name: "Post-Conference Webinar Package", quantity: 1, price: "5000" },
    { id: 9473, name: "Logo Sponsorship", quantity: 1, price: "2000" },
    { id: 9475, name: "Lunch Sponsorship", quantity: 1, price: "2450" },
    { id: 17630, name: "Coffee Bar Sponsorship", quantity: 1, price: "1600" }
  ];  

const idArray = data.map(item => item.id);

if (idArray.includes(idToCheck)) {
  console.log(`${idToCheck} exists in the array.`);
} else {
  console.log(`${idToCheck} does not exist in the array.`);
}
