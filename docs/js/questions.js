// Sample questions for each category
const questions = {
    general: [
        {
            id: 1,
            question: "What is the capital of France?",
            options: ["London", "Berlin", "Paris", "Madrid"],
            correctAnswer: 2,
            explanation: "Paris is the capital city of France."
        },
        {
            id: 2,
            question: "Which planet is known as the Red Planet?",
            options: ["Venus", "Mars", "Jupiter", "Saturn"],
            correctAnswer: 1,
            explanation: "Mars is called the Red Planet due to its reddish appearance."
        },
        {
            id: 3,
            question: "Who painted the Mona Lisa?",
            options: ["Vincent van Gogh", "Pablo Picasso", "Leonardo da Vinci", "Michelangelo"],
            correctAnswer: 2,
            explanation: "The Mona Lisa was painted by Leonardo da Vinci between 1503 and 1519."
        }
    ],
    science: [
        {
            id: 4,
            question: "What is the chemical symbol for gold?",
            options: ["Ag", "Fe", "Au", "Cu"],
            correctAnswer: 2,
            explanation: "Au (Aurum in Latin) is the chemical symbol for gold."
        },
        {
            id: 5,
            question: "What is the largest organ in the human body?",
            options: ["Heart", "Brain", "Liver", "Skin"],
            correctAnswer: 3,
            explanation: "The skin is the largest organ in the human body."
        },
        {
            id: 6,
            question: "What is the speed of light?",
            options: ["299,792 km/s", "199,792 km/s", "399,792 km/s", "499,792 km/s"],
            correctAnswer: 0,
            explanation: "Light travels at approximately 299,792 kilometers per second in a vacuum."
        }
    ],
    history: [
        {
            id: 7,
            question: "In which year did World War II end?",
            options: ["1943", "1944", "1945", "1946"],
            correctAnswer: 2,
            explanation: "World War II ended in 1945 with the surrender of Germany and Japan."
        },
        {
            id: 8,
            question: "Who was the first President of the United States?",
            options: ["John Adams", "Thomas Jefferson", "Benjamin Franklin", "George Washington"],
            correctAnswer: 3,
            explanation: "George Washington served as the first President of the United States from 1789 to 1797."
        },
        {
            id: 9,
            question: "Which ancient wonder was located in Alexandria?",
            options: ["The Colossus", "The Lighthouse", "The Hanging Gardens", "The Pyramids"],
            correctAnswer: 1,
            explanation: "The Lighthouse (Pharos) of Alexandria was one of the Seven Wonders of the Ancient World."
        }
    ],
    geography: [
        {
            id: 10,
            question: "Which is the largest continent by area?",
            options: ["Africa", "North America", "Asia", "Europe"],
            correctAnswer: 2,
            explanation: "Asia is the largest continent by area, covering approximately 44.5 million square kilometers."
        },
        {
            id: 11,
            question: "What is the longest river in the world?",
            options: ["Amazon", "Nile", "Yangtze", "Mississippi"],
            correctAnswer: 1,
            explanation: "The Nile River is the longest river in the world, stretching 6,650 kilometers."
        },
        {
            id: 12,
            question: "Which country has the most islands in the world?",
            options: ["Indonesia", "Japan", "Philippines", "Sweden"],
            correctAnswer: 3,
            explanation: "Sweden has the most islands in the world, with over 267,570 islands."
        }
    ],
    technology: [
        {
            id: 13,
            question: "Who is the co-founder of Microsoft?",
            options: ["Steve Jobs", "Bill Gates", "Mark Zuckerberg", "Jeff Bezos"],
            correctAnswer: 1,
            explanation: "Bill Gates co-founded Microsoft with Paul Allen in 1975."
        },
        {
            id: 14,
            question: "What does CPU stand for?",
            options: ["Central Processing Unit", "Computer Personal Unit", "Central Program Utility", "Computer Processing Unit"],
            correctAnswer: 0,
            explanation: "CPU stands for Central Processing Unit, which is the primary component of a computer that processes instructions."
        },
        {
            id: 15,
            question: "In what year was the first iPhone released?",
            options: ["2005", "2006", "2007", "2008"],
            correctAnswer: 2,
            explanation: "The first iPhone was released by Apple in 2007."
        }
    ]
}; 