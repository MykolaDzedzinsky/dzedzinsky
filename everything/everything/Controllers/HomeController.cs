using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace everything.Controllers
{
    public class HomeController : Controller
    {
        public ActionResult Index()
        {
            ViewBag.Message = string.Format("{0}::{1}  {2}", RouteData.Values["controller"], RouteData.Values["action"],
                                            RouteData.Values["id"]);
            ViewBag.Title = "Головна";
            return View();
        }

        public ActionResult About()
        {
            ViewBag.Message = "Your app description page.";
            ViewBag.Title = "Про нас";
            return View();
        }

        public ActionResult Contact()
        {
            ViewBag.Message = "Your contact page.";
            ViewBag.Title = "Звя'зок";
            return View();
        }


        public ActionResult Games()
        {
            ViewBag.Message = "Your games page.";
            ViewBag.Title = "Ігри";
            return View();
        }

        public ActionResult Chat()
        {
            ViewBag.Message = "Your chat page.";
            ViewBag.Title = "Чат";
            return View();
        }

        public ActionResult Faq()
        {
            ViewBag.Message = "Your Faq page.";
            ViewBag.Title = "FAQ";
            return View();
        }

        public ActionResult Forum()
        {
            ViewBag.Message = "Your forum page.";
            ViewBag.Title = "Форум";
            return View();
        }

        public ActionResult Rules()
        {
            ViewBag.Message = "Your rules page.";
            ViewBag.Title = "Правила";
            return View();
        }

        public ActionResult Top()
        {
            ViewBag.Message = "Your top page.";
            ViewBag.Title = "Топ";
            return View();
        }
    }
}