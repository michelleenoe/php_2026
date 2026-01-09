import { burgerMenu } from "./burger-menu.js";

import { flipBtn } from "./flip-btn.js";
import { setupRepostButtons } from "./repost.js";
import { setupFeedInfiniteScroll } from "./feed.js";
import { hydratePostImages } from "./post-images.js";

import { searchOverlay } from "./search.js";

import "./validation.js";

burgerMenu();
flipBtn();
setupRepostButtons();
searchOverlay();
setupFeedInfiniteScroll();
hydratePostImages();
